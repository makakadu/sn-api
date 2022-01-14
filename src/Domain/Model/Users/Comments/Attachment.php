<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Comments;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Comments\ProfileComment;
use App\Domain\Model\DomainException;

abstract class Attachment {
    use \App\Domain\Model\EntityTrait;
    
    protected User $owner;
    protected User $creator;
    protected ?string $commentId = null;
    
    protected bool $isDeletedFromComment = false;
    protected bool $isDeleted = false;
    protected bool $isDeletedByGlobalManager = false;
    protected ?\DateTime $deletedAt = null;
    protected ?\DateTime $deletedByGlobalManagerAt = null;
    
    function __construct(User $creator) {
        $this->id = (string) \Ulid\Ulid::generate(true);
        $this->creator = $creator;
        $this->owner = $creator;
        $this->createdAt = new \DateTime("now");
    }
    
    function owner(): User {
        return $this->owner;
    }

    function commentId(): ?string {
        return $this->commentId;
    }
    
    function isDeleted(): bool {
        return $this->isDeleted;
    }
    
    function isDeletedByGlobalManager(): bool {
        return $this->isDeletedByGlobalManager;
    }
    
    abstract function type(): string;
    
    function setComment(ProfileComment $comment): void {
//        if(!$this->creator->equals($comment->creator())) { // Эту проверку можно сделать и раньше, но всё-таки это бизнес логика и её место здесь
//            throw new DomainException("Photo can be added to comment if creator of comment and creator of photo are same");
//        }
        if($this->commentId && $this->commentId !== $comment->id()) {
            throw new DomainException("Already added to another comment");
        }
        $this->commentId = $comment->id();
    }
    
    function delete(User $initiator, bool $asGlobalManager): void {
        if($this->commentId) { // После того как прикрепление было добавлено к комментарию, его нельзя мягко удалить, но зато можно мягко удалить коммент
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
            if(!$this->creator->equals($initiator) && !$this->owner->equals($initiator)) {
                throw new DomainException("No rigths to softly delete");
            }
            if($this->isDeleted === false) {
                $this->deletedAt = new \DateTime('now');
            }
            $this->isDeleted = true;
        }
    }
    
    // template T указывает на то, что это дженерик метод
    // @param CommentAttachmentVisitor <T> $visitor значит, что метод принимает объект типа CommentAttachmentVisitor, T - это тип, которым типизирован CommentAttachmentVisitor
    // и поскольку этот метод также типизирован буквой T и возвращает T, то метод будет типизирован этим типом.
    // Метод должен вернуть сущность типа T
    // В Java сигнатура метода выглядела бы так:
    // public <T> abstract T accept (CommentAttachmentVisitor<T> v)
    // Где <T> - это template T, T - это возвращаемое значение
    
    /**
     * @template T
     * @param CommentAttachmentVisitor <T> $visitor
     * @return T
     */
    abstract function acceptAttachmentVisitor(CommentAttachmentVisitor $visitor);
}
