<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Post;

use App\Domain\Model\Users\User\User;

class Reaction extends \App\Domain\Model\Groups\GroupReaction {
    
    private Post $post;
    private ?string $groupId;
  
    function __construct(User $creator, Post $post, string $type, bool $onBehalfOfGroup) {
        parent::__construct($creator, $post->owningGroup(), $type, $onBehalfOfGroup);
        $this->post = $post;
        $this->groupId = $onBehalfOfGroup ? $this->owningGroup->id() : null;
    }
    
    function post(): Post {
        return $this->post;
    }

    public function reacted(): \App\Domain\Model\Common\Reactable {
        return $this->post;
    }

}