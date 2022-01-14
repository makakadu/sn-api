<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Shares;

use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Pages\Post\Post;
use App\Domain\Model\Users\User\User;

class SharedPagePost extends Shared {
    
    private ?Post $post; // Удобно, если лайк будет передаваться с нового поста на оригинал
    private ?User $creator;
    private ?Page $page;
    
    function __construct(Post $post) {
        $this->creator = $post->creator();
        $this->page = $post->owningPage();
        $this->post = $post;
        $this->originalId = $post->id();
        $this->createdAt = new \DateTime('now');
        $this->originalCreatedAt = $post->createdAt();
    }

    public function acceptSharedVisitor(SharedVisitor $visitor) {
        return $visitor->visitSharedPagePost($this);
    }
    
    public function post(): ?Post {
        return $this->post;
    }
    
    public function page(): ?Page {
        return $this->page;
    }
    
    function creator(): ?User {
        return $this->creator;
    }

    public function shared(): ?Shareable {
        return $this->post;
    }
}
