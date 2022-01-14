<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Post;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Users\ProfileReaction;

class Reaction extends ProfileReaction {
    
    private ?Post $post;
    
    function __construct(User $creator, Post $post, int $type) {//, ?Page $asPage) {
        $this->id = (string)\Ulid\Ulid::generate(true);
        $this->creator = $creator;
        $this->owner = $post->creator();
        $this->post = $post;
        
//        $this->asPage = $asPage;
//        $this->pageId = $asPage ? $asPage->id() : "";
        
        $this->reactionType = $type;
        $this->createdAt = new \DateTime('now');
    }
    
    function edit(int $type): void {
        $this->changeReactionType($type);
    }
    
    function post(): Post {
        return $this->post;
    }

    public function reacted(): \App\Domain\Model\Common\Reactable {
        return $this->post;
    }

    function deleteFromPost(): void {
        $this->post = null;
    }

}