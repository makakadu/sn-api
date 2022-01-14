<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Shares;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Post\Post;
use App\Domain\Model\Users\Post\Attachment;

class SharedUserPost extends Shared {
    
    private ?Post $post;
    private ?User $creator;
            
    function __construct(Post $post) {
        $this->creator = $post->creator();
        $this->post = $post;
        $this->originalId = $post->id();
        $this->createdAt = new \DateTime('now');
        $this->originalCreatedAt = $post->createdAt();
    }

    public function acceptSharedVisitor(SharedVisitor $visitor) {
        return $visitor->visitSharedUserPost($this);
    }
    
    public function post(): ?Post {
        return $this->post;
    }

    public function creator(): ?User {
        return $this->creator;
    }

    public function shared(): ?Shareable {
        return $this->post;
    }

}
