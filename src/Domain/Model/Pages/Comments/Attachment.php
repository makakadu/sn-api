<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Comments;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Comments\CommentAttachmentVisitor;
use App\Domain\Model\Pages\Comments\PageComment;
use App\Domain\Model\DomainException;
use App\Domain\Model\Pages\Page\Page;

abstract class Attachment {
    use \App\Domain\Model\EntityTrait;
    
    private Page $owningPage;
    private User $creator;
    //private bool $isDeletedFromComment = false; // Когда прикрепление прикреплено к комменту и заменяется другим, то это свойство должно стать true, это будет знаком для
    // сборщика мусора, что порикрепление можно удалять из БД. Если это свойство === true, то прикрепление будет считаться не найденым при его запросе.
    // Возможно это даже лучший вариант, чем orphanremoval, потому что я не уверен, что orphanremoval будет работать как мне хочется
    private ?string $commentId = null;
    //private bool $isDetached = false;
    
    protected bool $isDeletedFromComment = false;
    private bool $isDeleted = false;
    private bool $isDeletedByGlobalManager = false;
    private ?\DateTime $deletedAt = null;
    private ?\DateTime $deletedByGlobalManagerAt = null;
    
    function __construct(User $creator, Page $owningPage) {
        $this->id = (string) \Ulid\Ulid::generate(true);
        $this->creator = $creator;
        $this->owningPage = $owningPage;
        $this->createdAt = new \DateTime("now");
    }
    
    function detach(): void {
        $this->isDeleted = true;
        $this->deletedAt = new \DateTime('now');
    }
    
    public function creator(): User {
        return $this->creator;
    }
    
    function owningPage(): Page {
        return $this->owningPage;
    }
    
    function isDeleted(): bool {
        return $this->isDeleted;
    }
    
    function isDeletedByGlobalManager(): bool {
        return $this->isDeletedByGlobalManager;
    }
    
    abstract function type(): string;
    
    function setComment(PageComment $comment): void {
        if($this->commentId && $this->commentId !== $comment->id()) {
            throw new DomainException("Already added to another comment");
        }
        $this->commentId = $comment->id();
    }
    
    function delete(User $initiator, bool $asGlobalManager): void {
        if($this->commentId) {
            throw new DomainException("Cannot softly delete after it has been added to comment");
        }
        
        if($asGlobalManager) {
            if(!$initiator->isGlobalManager()) {
                throw new DomainException("Cannot softly delete as global manager");
            }
            if($this->isDeletedByGlobalManager === false) {
                $this->deletedByGlobalManagerAt = new \DateTime('now');
            }
            $this->isDeletedByGlobalManager = true;
        }
        else {
            if(!$this->creator->equals($initiator)) {
                throw new DomainException("Cannot softly delete post created by another member");
            }
            if($this->isDeleted === false) {
                $this->deletedAt = new \DateTime('now');
            }
            $this->isDeleted = true;
        }
    }
    
    /**
     * @template T
     * @param CommentAttachmentVisitor <T> $visitor
     * @return T
     */
    abstract function acceptAttachmentVisitor(CommentAttachmentVisitor $visitor);
}
