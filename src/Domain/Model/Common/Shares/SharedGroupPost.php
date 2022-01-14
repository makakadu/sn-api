<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Shares;

use App\Domain\Model\Groups\Group\Group;
use App\Domain\Model\Groups\Post\Post;
use App\Domain\Model\Users\User\User;

class SharedGroupPost extends Shared {
    
    private ?Post $post; // Удобно, если лайк будет передаваться с нового поста на оригинал
    private ?User $creator;
    private ?Group $group;
    private bool $onBehalfOfGroup;
    
    function __construct(Post $post) {
        $this->creator = $post->creator();
        $this->group = $post->owningGroup();
        $this->post = $post;
        $this->onBehalfOfGroup = $post->onBehalfOfGroup();
        $this->originalId = $post->id();
        $this->createdAt = new \DateTime('now');
        $this->originalCreatedAt = $post->createdAt();
    }

    public function acceptSharedVisitor(SharedVisitor $visitor) {
        return $visitor->visitSharedGroupPost($this);
    }
    
    public function post(): ?Post {
        return $this->post;
    }
    
    public function group(): ?Group {
        return $this->group;
    }
    
    public function creator(): ?User {
        return $this->creator;
    }
    
    public function onBehalfOfGroup(): bool {
        return $this->onBehalfOfGroup;
    }

    public function shared(): ?Shareable {
        return $this->post;
    }
}
