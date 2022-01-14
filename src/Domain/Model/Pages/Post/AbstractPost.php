<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Post;

use App\Domain\Model\Common\PostTrait;
use App\Domain\Model\Common\PostVisitor;
use App\Domain\Model\Common\Shares\Shared;
use App\Domain\Model\DomainException;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ulid\Ulid;
use App\Domain\Model\Pages\Post\Comment\Comment;
use App\Domain\Model\Pages\Comments\Attachment as CommentAttachment;
use App\Application\Exceptions\ForbiddenException;
use App\Domain\Model\Saveable;
use App\Domain\Model\Common\Reactable;
use App\Domain\Model\Common\Shares\Shareable;
use App\Domain\Model\Pages\PageReaction;
use App\Domain\Model\Pages\Comments\PageComment;

abstract class AbstractPost  {
    use \App\Domain\Model\Pages\PageEntity;
    
    protected User $creator;
    protected bool $addSignature;
    protected string $id;
    protected string $text;
    
    /** @var Collection<string, Attachment> $attachments */
    protected Collection $attachments;
    
    protected ?\DateTime $deletedAt = null;
    protected bool $deleted = false;
    
    protected \DateTime $createdAt;
    
    /**
     * @param array<mixed> $attachments
     */
    function __construct(
        Page $owningPage,
        User $creator,
        string $text,
        array $attachments,
        bool $addSignature
    ) {
        $this->id = Ulid::generate(true).'g';
        $this->attachments = new ArrayCollection();
        $this->owningPage = $owningPage;
        $this->creator = $creator;
        $this->text = $text;
        $this->setAttachments($attachments);
        $this->text = $text;
        $this->addSignature = $addSignature;
        $this->createdAt = new \DateTime('now');
    }
    
    function text(): string {
        return $this->text;
    }
    
    /**
     * @return Collection<string, Attachment>
     */
    function attachments(): Collection {
        return $this->attachments;
    }
    
    public function addSignature(): bool {
        return $this->addSignature;
    }
    
    /**
     * @param array<int, Attachment> $attachments
     */
    protected function setAttachments(array $attachments): void {
        /** @var Attachment $attachment */
        foreach ($attachments as $attachment) {
            if(!$this->creator->equals($attachment->creator())) {
                $attachmentType = $attachment->type();
                throw new DomainException("Cannot add $attachmentType attachment created by someone else");
            }
            $attachment->setPost($this);
        }
    }
    
//    // Пост может быть создан только от имени страницы и его могут удалять только менеджеры страницы и глобальные менеджеры, если $byGlobalManager === true, то значит пост удаляется менеджером
//    // страницы
//    function delete(bool $asGlobalManager): void {
//        if($asGlobalManager) {
//            // Если пост уже мягко удалён менеджерами страницы, то он может быть также мягко удалён глобальными менеджерами, в таком случае менеджеры страницы не смогут восстановить
//            // пост, пока его не восстановят глобальные менеджеры.
//            $this->isDeletedByGlobalManager = true;
//            if(!$this->deletedByGlobalManagerAt) { // Это нужно, если перед этим свойство $isDeletedByGlobalManager уже было true, то есть в случае, если это свойство не изменяется
//                $this->deletedByGlobalManagerAt = new \DateTime('now');
//            }
//        } else {
//            // Если менеджер страницы хочет мягко удалить пост и пост уже мягко удалён глобальными менеджерами, то будет выброшено исключение.
//            if($this->isDeletedByGlobalManager) {
//                throw new DomainException("Cannot soft delete post because it softly deleted by global manager");
//            }
//            $this->isDeleted = true;
//        }
//        $this->deletedAt = new \DateTime('now');
//    }
//    
//    function restore(bool $asGlobalManager): void {
//        if($asGlobalManager) {
//            $this->isDeletedByGlobalManager = false;
//        } else {
//            if($this->isDeletedByGlobalManager) {
//                throw new DomainException("Cannot change property \$isDeleted to false while \$isDeletedByGlobalManager is true");
//            }
//            $this->isDeleted = false;
//        }
//    }
    
    function isDeleted(): bool {
        return $this->deleted;
    }
    
    function creator(): User {
        return $this->creator;
    }

}
