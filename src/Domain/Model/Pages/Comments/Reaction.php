<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Comments;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Page\Page;

class Reaction extends \App\Domain\Model\Pages\PageReaction {
    
    private PageComment $comment;
    private bool $onBehalfOfPage;
    private ?string $pageId;
    
    function __construct(User $creator, PageComment $photo, string $type, bool $onBehalfOfPage) {
        if(!in_array($type, $this->reactionsTypes())) {
            throw new \InvalidArgumentException("Value of type should be from 1 to 8");
        }
        $this->id = (string)\Ulid\Ulid::generate(true);
        $this->creator = $creator;
        $this->owningPage = $photo->owningPage();
        $this->comment = $photo;
        
        $this->onBehalfOfPage = $onBehalfOfPage;
        $this->pageId = $onBehalfOfPage ? $this->owningPage->id() : null;
        
        $this->reactionType = $type;
        $this->createdAt = new \DateTime('now');
    }
    
    function edit(User $initiator, string $type): void {
        if($this->onBehalfOfPage && !$this->owningPage->isAdminOrEditor($initiator)) {
            throw new \App\Domain\Model\DomainException("No rights to edit reaction created on behalf of page");
        } elseif(!$this->onBehalfOfPage && !$this->creator->equals($initiator)) {
            throw new \App\Domain\Model\DomainException("No rights to edit reaction created by another user");
        }
        $this->changeReactionType($type);
    }
    
    function comment(): PageComment {
        return $this->comment;
    }

    public function reacted(): \App\Domain\Model\Common\Reactable {
        return $this->comment;
    }

}