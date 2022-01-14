<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Post;

use App\Domain\Model\Users\User\User;

abstract class Attachment {
    use \App\Domain\Model\EntityTrait;
    
    protected User $owner;
    protected User $creator;
    protected ?Post $post;
    
    protected bool $isDeleted = false;
    protected ?\DateTime $deletedAt;
    
    function __construct(User $creator) {
        $this->id = (string) \Ulid\Ulid::generate(true);
        $this->creator = $creator;
        $this->owner = $creator;
        $this->createdAt = new \DateTime("now");
    }

    abstract function setPost(Post $post): void;
    
    function removeFromPost(): void {
        $this->post = null;
        $this->isDeleted = true;
        $this->deletedAt = new \DateTime('now');
    }
    
    function creator(): User {
        return $this->creator;
    }
    
    function owner(): User {
        return $this->creator;
    }

    function post(): ?Post {
        return $this->post;
    }
    
    abstract function type(): string;
    
    /**
     * @template T
     * @param AttachmentVisitor <T> $visitor
     * @return T
     */
    abstract function acceptAttachmentVisitor(AttachmentVisitor $visitor);
}
