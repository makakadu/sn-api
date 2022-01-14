<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Comments;

use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Pages\PageReaction;

use App\Domain\Model\Common\Reactable;

abstract class PageComment implements \App\Domain\Model\Common\Comments\Comment, Reactable {
    use \App\Domain\Model\EntityTrait;
    use \App\Domain\Model\Pages\PageEntity;
    use \App\Domain\Model\Common\Comments\CommentTrait;
    
    protected User $creator;

    protected ?\DateTime $deletedAt = null;
    protected bool $isDeleted = false;
    protected bool $isDeletedByLocalManager = false;
    protected bool $isDeletedByGlobalManager = false;
    
    protected ?Attachment $attachment;
    
    /** @var Collection<string, Reaction> $reactions */
    protected Collection $reactions;
    
    /** @var Collection<string, self> $replies */
    protected Collection $replies;
    
    protected ?self $root;
    protected ?string $repliedId;
    
    public function __construct(User $creator, string $text, Page $owningPage, ?Attachment $attachment) {
        $this->id = (string)\Ulid\Ulid::generate(true);
        $this->creator = $creator;
        $this->text = $text;
        $this->owningPage = $owningPage;
        $this->attachment = $attachment;
        $this->createdAt = new \DateTime('now');
    }
//    
//    public function onBehalfOfPage(): ?Page {
//        return $this->asPage;
//    }
        
//    function edit(string $text, ?Attachment $attachment): void {
//        $this->changeText($text);
//        $this->attachment = $attachment;
//    }
    
    public function attachment(): ?Attachment {
        return $this->attachment;
    }
    
    abstract function repliesCount(): int;

    /** @return Collection<string, self> */
    abstract function replies(): Collection;
    
    /** @return Collection<string, PageReaction> */
    abstract function reactions(): Collection;
    
    function isDeletedByLocalManager(): bool {
        return $this->isDeletedByLocalManager;
    }
    
    function isDeletedByGlobalManager(): bool {
        return $this->isDeletedByGlobalManager;
    }
    
    function isDeletedByMember(): bool {
        return $this->isDeleted;
    }
    
    public function creator(): User {
        return $this->creator;
    }
    
    function root(): ?self {
        return $this->root;
    }

    function repliedId(): ?string {
        return $this->repliedId;
    }
    
    /**
     * @template T
     * @param \App\Domain\Model\Common\ReactableVisitor <T> $visitor
     * @return T
     */
    public function acceptReactableVisitor(\App\Domain\Model\Common\ReactableVisitor $visitor) {
        return $visitor->visitPageComment($this);
    }

    
    /**
     * @template T
     * @param PageCommentVisitor <T> $visitor
     * @return T
     */
    abstract function acceptPageCommentVisitor(PageCommentVisitor $visitor);

}
