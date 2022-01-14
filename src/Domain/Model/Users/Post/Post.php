<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Post;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Users\User\User;
use App\Application\Errors;
use App\Domain\Model\Users\Post\Comment\Comment;
use App\Domain\Model\Common\PostVisitor;
use App\Domain\Model\Common\Shares\Shared;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\DomainException;
use App\Application\Exceptions\ForbiddenException;
use App\Domain\Model\Users\Comments\Attachment as CommentAttachment;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Saveable;
use App\Domain\Model\Common\Reactable;
use App\Domain\Model\Common\Shares\Shareable;
use App\Domain\Model\Users\ProfileReaction;
use App\Domain\Model\Users\Comments\ProfileComment;

class Post implements \App\Domain\Model\Common\PostInterface, Saveable, Shareable, Reactable {
    use \App\Domain\Model\EntityTrait;
    use \App\Domain\Model\Common\PostTrait;
    use \App\Domain\Model\Users\ProfileEntity;
    
    const MEDIA_COUNT = 10;
    const TEXT_LENGTH = 300;
    
   // private User $creator;
    
    private bool $isPublic = true; // Мне кажется, что этого свойства достаточно для приватности. Если хочешь что-либо показать определённому кругу людей, то можно сделать это
    // в личном сообщении или в групповом чате. Возможно я добавлю более сложные настройки, но потом
    
    /** @var Collection<string, Attachment> $attachments */
    private Collection $attachments;
    
    /** @var Collection<string, Comment> $comments */
    private Collection $comments;
    /** @var Collection<string, ProfileReaction> $reactions */
    private Collection $reactions;
    
    private ?Shared $shared;

    private bool $deleted = false;
    private bool $deletedByGlobalModeration = false;
    private ?\DateTime $deletedAt = null;
    private ?\DateTime $deletedByGlobalModerationAt = null;
    
    /**
     * @param array<Attachment> $attachments
     * @throws DomainException
     */
    function __construct(
        User $creator,
        string $text,
        ?Shared $shared,
        bool $disableComments,
        bool $disableReactions,
        bool $public,
        $attachments
    ) {
        $attachmentsCount = count($attachments);
        
        if($shared && $attachmentsCount > 1) {
            throw new DomainException('Only one attachment can be in post with shared');
        } elseif(!$shared && $attachmentsCount > 10) {
            throw new DomainException('Max 10 attachments can be in post');
        }
        $this->id = (string)\Ulid\Ulid::generate(true);
        $this->owner = $creator;
        $this->creator = $creator;
        $this->shared = $shared;
        
        $this->attachments = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->reactions = new ArrayCollection();
        
        $this->setAttachments($attachments);
        $this->changeText($text);
        $this->disableComments = $disableComments;
        $this->disableReactions = $disableReactions;
        $this->isPublic = $public;
        $this->createdAt = new \DateTime('now');
    }
    
    function deleteReaction(string $reactionId): void {
        $reaction = $this->reactions->get($reactionId);
        $reaction->deleteFromPost();
        $this->reactions->remove($reactionId);
    }
    
    function isSoftlyDeleted(): bool {
        return $this->deleted || $this->deletedByGlobalModeration;
    }
    
    /**
     * @param array<int, Attachment> $attachments
     */
    private function setAttachments(array $attachments): void {
        /** @var Attachment $attachment */
        foreach ($attachments as $attachment) {
            if(!$this->creator->equals($attachment->creator())) {
                $attachmentType = $attachment->type();
                throw new DomainException("Cannot add $attachmentType attachment created by someone else");
            }
            $this->attachments->add($attachment);
            $attachment->setPost($this);
        }
    }
    
    function comment(User $creator, string $text, ?string $repliedId, ?CommentAttachment $attachment, ?Page $asPage): Comment {
        if($this->deleted || $this->deletedByGlobalModeration) {
            throw new DomainException("Cannot comment because it is softly deleted");
        } elseif($this->disableComments) {
            throw new DomainException("Comments are disabled");
        } elseif ($this->comments->count() >= 4000) {
            throw new DomainException("Max number(4000) of comments has been reached");
        } elseif(!$this->isPublic && !$this->creator->equals($creator) && !$this->creator->isConnectedWith($creator)) {
            throw new DomainException("No rights to comment");
        } elseif($asPage && !$asPage->isAllowedForExternalActivity()) { /* Если страница не набрала достаточное число подписчиков, плохо оформлена и так далее, то внешняя активность запрещена.
             * Это защита от страниц, которые созданы для сомнительных действий
             * Возможно стоит перенести в сервис авторизации по нескольким причинам:
             * 1. Возможно нужно будет использовать репозитории
             * 2. Код будет повторяться во многих местах
             */
            throw new DomainException("Cannot comment on behalf of given page because commenting in profiles is not allowed for this page");
        } elseif($asPage && !$asPage->isAdminOrEditor($creator)) {
            throw new DomainException("No rights to comment on behalf of given page");
        } 
        $replied = null;
        if($repliedId) {
            $replied = $this->comments->get($repliedId);
            if(!$replied) {
                throw new DomainException("Cannot replied to comment $repliedId, no such comment found");
            }
        }
        $comment = new Comment($this, $creator, $text, $replied, $attachment, $asPage);
        $this->comments->add($comment);
        return $comment;
    }
    
    function react(User $reactor, int $type): Reaction { //, ?Page $asPage): void {
        if($this->disableReactions) {
            throw new ForbiddenException(111, "Reactions are disabled");
        }
//        if($asPage && !$asPage->isAllowedForExternalActivity()) {
//            throw new DomainException("Cannot react on behalf of given page because reacting in profiles is not allowed for this page");
//        }
//        if($asPage && !$asPage->isAdminOrEditor($reactor)) {
//            throw new DomainException("No rights to react on behalf of given page");
//        }
        
        /** @var ArrayCollection<string, Reaction> $reactions */
        $reactions = $this->reactions;
        
        $criteria = Criteria::create()->where(Criteria::expr()->eq("creator", $reactor));
        $matched = $reactions->matching($criteria);
        
        if($matched->count()) {
            //throw new DomainException("User {$reactor->id()} already reacted to this post");
            throw new \App\Domain\Model\DomainExceptionAlt(['errorCode' => 228, 'message' => "User {$reactor->id()} already reacted to this post", "reactionId" => $matched[0]->id()]);
        }
        $reaction = new Reaction($reactor, $this, $type);//, $asPage);
        $this->reactions->add($reaction);
        return $reaction;
    }
    
    /**
     * @param array<int, Attachment> $attachments
     * @throws DomainException
     */
    function edit(string $text, bool $disableComments, bool $disableReactions, array $attachments, bool $isPublic): void {
        $attachmentsCount = count($attachments);
        
        if(!\strlen($text) && !count($attachments)) {
            throw new DomainException('Text and attachments cannot be empty');
        }
        if($this->shared && $attachmentsCount > 1) {
            throw new DomainException('Only one attachment can be in post with shared');
        } elseif(!$this->shared && $attachmentsCount > 10) {
            throw new DomainException('Max 10 attachments can be in post');
        }
        if((int)(new \DateTime('now'))->diff($this->createdAt)->format('%a') > 0) { // a - это дни
            throw new DomainException('Время редактирования закончено');
        }
        
        $this->removeCurrentAttachments();
        $this->setAttachments($attachments);
        $this->changeText($text);
        $this->disableComments = $disableComments;
        $this->disableReactions = $disableReactions;
        $this->isPublic = $isPublic;
    }
    
    public function removeCurrentAttachments() {
        /** @var Attachment $attachment */
        foreach ($this->attachments as $attachment) {
            $attachment->removeFromPost();
        }
    }
    
    /**
     * @return Collection<string, Attachment>
     */
    function attachments(): Collection {
        return $this->attachments;
    }
    
    /**
     * @return Collection <string, Comment>
     */
    function comments(): Collection {
        return $this->comments;
    }
    
    /**
     * @return Collection <string, ProfileReaction>
     */
    function reactions(): Collection {
        return $this->reactions;
    }
    
    function isPublic(): bool {
        return $this->isPublic;
    }
    
    function equals(self $post): bool {
        return $this->id === $post->id;
    }
    
    function delete(User $initiator): void {
        if(!$this->creator->equals($initiator)) {
            throw new DomainException("No rigths to softly delete");
        }
        if($this->deleted === false) {
            $this->deletedAt = new \DateTime('now');
        }
        $this->deleted = true;
    }
    
    function deleteByGlobalModer(User $initiator): void {
        if(!$initiator->isGlobalManager()) {
            throw new DomainException("No rigths to softly delete as global manager");
        }
        if($this->deletedByGlobalModeration === false) {
            $this->deletedByGlobalModerationAt = new \DateTime('now');
        }
        $this->deletedByGlobalModeration = true;
    }
    
    function restore(User $initiator): void {
        if(!$this->creator->equals($initiator)) {
            throw new DomainException("No rigths to restore");
        }
        $this->deletedAt = null;
        $this->deleted = false;
    }
    
    function restoreAsManager(User $initiator): void {
        if(!$initiator->isGlobalManager()) {
            throw new DomainException("No rigths to restore as global manager");
        }
        $this->deletedAt = null;
        $this->deletedByGlobalModeration = false;
    }
    
    function shared(): ?Shared {
        return $this->shared;
    }
    
    /**
     * @return mixed
     */
    function accept(PostVisitor $visitor) {
        return $visitor->visitProfilePost($this);
    }
    
    function creator(): User {
        return $this->creator;
    }

    public function acceptReactableVisitor(\App\Domain\Model\Common\ReactableVisitor $visitor) {
        return $visitor->visitUserPost($this);
    }

    public function acceptSaveableVisitor(\App\Domain\Model\SaveableVisitor $visitor) {
        return $visitor->visitUserPost($this);
    }



}