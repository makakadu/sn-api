<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Post;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\DomainException;

abstract class Attachment {
    use \App\Domain\Model\EntityTrait;
    
    protected Page $owningPage;
    protected User $creator;
    protected ?Post $post;
    
    protected bool $isDeleted;
    protected bool $isDeletedByLocalManager;
    protected bool $isDeletedByGlobalManager;
    protected ?\DateTime $deletedAt = null;
    
    function __construct(User $creator, Page $owningPage) {
        $this->id = (string) \Ulid\Ulid::generate(true);
        $this->creator = $creator;
        $this->owningPage = $owningPage;
        $this->createdAt = new \DateTime("now");
    }
    
    function setPost(Post $post): void {
        if($this->post && !$this->post->equals($post)) {
            throw new DomainException("Cannot be added to another post");
        }
        $this->post = $post;
    }
    
    function creator(): User {
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
