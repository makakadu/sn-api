<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\SavesCollection\GroupItems;

use App\Domain\Model\Users\SavesCollection\SavedItem;
use App\Domain\Model\Groups\Videos\Video;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Group\Group;
use App\Domain\Model\Users\SavesCollection\SavesCollection;

class GroupVideoItem extends SavedItem {

    /*
     * свойства $photo, $owningGroup и $creator являются nullable, потому что фото, группа и создатель могут быть удалены. $creator также nullable из-за того, что фото может быть
     * создано от имени группы
     */
    private ?Video $video;
    private ?Group $group;
    private bool $onBehalfOfGroup;
    private ?User $creator;
            
    function __construct(SavesCollection $collection, Video $video) {
        parent::__construct($collection, $video->createdAt);
        $this->originalId = $video->id();
        $this->video = $video;
        $this->creator = $video->creator();
        $this->group = $video->owningGroup();
        $this->onBehalfOfGroup = $video->onBehalfOfGroup();
        $this->originalCreatedAt = $video->createdAt();
    }

    public function saved(): ?\App\Domain\Model\Saveable {
        return $this->video;
    }

    public function acceptItemVisitor(\App\Domain\Model\Users\SavesCollection\SavedItemVisitor $visitor) {
        return $visitor->visitGroupVideoItem($this);
    }
    
    public function video(): ?Video {
        return $this->video;
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

    public function videoId(): string {
        return $this->videoId;
    }

    public function originalCreatedAt(): \DateTime {
        return $this->originalCreatedAt;
    }



}