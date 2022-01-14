<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Comments;

use App\Domain\Model\Users\User\User;

class Reaction extends \App\Domain\Model\Groups\GroupReaction {
    
    private GroupComment $comment;
    
    function __construct(User $creator, GroupComment $comment, string $type, bool $onBehalfOfGroup) {
        parent::__construct($creator, $comment->owningGroup(), $type, $onBehalfOfGroup);
        $this->comment = $comment;
    }
    
    function comment(): GroupComment {
        return $this->comment;
    }

    public function reacted(): \App\Domain\Model\Common\Reactable {
        return $this->comment;
    }

}