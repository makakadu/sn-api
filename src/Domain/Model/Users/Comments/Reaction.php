<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Comments;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Page\Page;

class Reaction extends \App\Domain\Model\Users\ProfileReaction {
    
    private ?ProfileComment $comment;
    
    function __construct(User $creator, ProfileComment $comment, int $type) {//, ?Page $asPage) {
        if(!in_array($type, [1, 2])) {
            throw new \InvalidArgumentException("Reaction type should be 1(like) or 2(dislike");
        }
        $this->id = (string)\Ulid\Ulid::generate(true);
        $this->creator = $creator;
        $this->owner = $comment->owner();
        $this->comment = $comment;
        
//        $this->asPage = $asPage;
//        $this->pageId = $asPage ? $asPage->id() : "";
        
        $this->reactionType = $type;
        $this->createdAt = new \DateTime('now');
    }
    
    function profileComment(): ProfileComment {
        return $this->comment;
    }

    public function reacted(): \App\Domain\Model\Common\Reactable {
        return $this->comment;
    }
    
    function deleteFromComment(): void {
        $this->comment = null;
    }

}