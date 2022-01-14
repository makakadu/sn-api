<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\SavesCollection\ProfileItems;

use App\Domain\Model\Users\SavesCollection\SavedItem;
use App\Domain\Model\Users\Post\Post;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\SavesCollection\SavesCollection;

class UserPostItem extends SavedItem {

    /*
     * свойства $photo, $owningGroup и $creator являются nullable, потому что фото, группа и создатель могут быть удалены. $creator также nullable из-за того, что фото может быть
     * создано от имени группы
     */
    private ?Post $post;
    private ?User $creator;
            
    function __construct(SavesCollection $collection, Post $post) {
        parent::__construct($collection, $post->createdAt(), 'post');
        $this->originalId = $post->id();
        $this->post = $post;
        $this->creator = $post->creator();
    }

    public function saved(): ?\App\Domain\Model\Saveable {
        return $this->post;
    }

    public function acceptItemVisitor(\App\Domain\Model\Users\SavesCollection\SavedItemVisitor $visitor) {
        return $visitor->visitUserPostItem($this);
    }
    
    public function post(): ?Post {
        return $this->post;
    }
    
    public function creator(): ?User {
        return $this->creator;
    }

    public function postId(): string {
        return $this->postId;
    }

    public function originalCreatedAt(): \DateTime {
        return $this->originalCreatedAt;
    }
}