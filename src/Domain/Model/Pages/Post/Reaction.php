<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Post;

use App\Domain\Model\Users\User\User;

class Reaction extends \App\Domain\Model\Pages\PageReaction {
    
    private Post $post;
    private bool $onBehalfOfPage;
    private ?string $pageId;
    
    function __construct(User $creator, Post $post, string $type, bool $onBehalfOfPage) {
        $this->id = (string)\Ulid\Ulid::generate(true);
        $this->creator = $creator;
        $this->owningPage = $post->owningPage();
        $this->post = $post;
        $this->reactionType = $type;
        $this->pageId = $onBehalfOfPage ? $this->owningPage->id() : null;
        $this->onBehalfOfPage = $onBehalfOfPage;
        $this->createdAt = new \DateTime('now');
    }

    public function reacted(): \App\Domain\Model\Common\Reactable {
        return $this->post;
    }
    
    function edit(User $initiator, string $type): void {
        if($this->onBehalfOfPage && !$this->owningPage->isAdminOrEditor($initiator)) {
            throw new \App\Domain\Model\DomainException("No rights to edit reaction created on behalf of page");
        } elseif(!$this->onBehalfOfPage && !$this->creator->equals($initiator)) {
            throw new \App\Domain\Model\DomainException("No rights to edit reaction created by another user");
        }
        $this->changeReactionType($type);
    }
    
    function onBehalfOfPage(): bool {
        return $this->onBehalfOfPage;
    }

}