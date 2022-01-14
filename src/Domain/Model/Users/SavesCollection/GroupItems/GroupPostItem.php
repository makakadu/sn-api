<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\SavesCollection\GroupItems;

use App\Domain\Model\Users\SavesCollection\SavedItem;
use App\Domain\Model\Groups\Post\Post;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Group\Group;
use App\Domain\Model\Users\SavesCollection\SavesCollection;

class GroupPostItem extends SavedItem {

    /*
     * свойства $photo, $owningGroup и $creator являются nullable, потому что фото, группа и создатель могут быть удалены. $creator также nullable из-за того, что фото может быть
     * создано от имени группы
     */
    private ?Post $post;
    private ?Group $group;
    private bool $onBehalfOfGroup;
    private ?User $creator;
            
    function __construct(SavesCollection $collection, Post $post) {
        parent::__construct($collection, $post->createdAt, 'post');
        $this->originalId = $post->id();
        $this->post = $post;
        $this->creator = $post->creator();
        $this->group = $post->owningGroup();
        $this->onBehalfOfGroup = $post->onBehalfOfGroup();
        $this->originalCreatedAt = $post->createdAt();
    }

    public function saved(): ?\App\Domain\Model\Saveable {
        return $this->post;
    }

    public function acceptItemVisitor(\App\Domain\Model\Users\SavesCollection\SavedItemVisitor $visitor) {
        return $visitor->visitGroupPostItem($this);
    }
    
    public function photo(): ?Post {
        return $this->post;
    }

    public function owningGroup(): ?Group {
        return $this->group;
    }

    public function onBehalfOfGroup(): bool {
        return $this->onBehalfOfGroup;
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