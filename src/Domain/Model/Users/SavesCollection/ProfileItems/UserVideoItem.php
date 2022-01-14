<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\SavesCollection\ProfileItems;

use App\Domain\Model\Users\SavesCollection\SavedItem;
use App\Domain\Model\Users\Videos\Video;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\SavesCollection\SavesCollection;

class UserVideoItem extends SavedItem {

    /*
     * свойства $photo, $owningGroup и $creator являются nullable, потому что фото, группа и создатель могут быть удалены. $creator также nullable из-за того, что фото может быть
     * создано от имени группы
     */
    private ?Video $video;
    private ?User $creator;
            
    function __construct(SavesCollection $collection, Video $video) {
        parent::__construct($collection, $video->createdAt(), 'video');
        $this->originalId = $video->id();
        $this->video = $video;
        $this->creator = $video->creator();
    }

    public function saved(): ?\App\Domain\Model\Saveable {
        return $this->video;
    }

    public function acceptItemVisitor(\App\Domain\Model\Users\SavesCollection\SavedItemVisitor $visitor) {
        return $visitor->visitUserVideoItem($this);
    }
    
    public function video(): ?Video {
        return $this->video;
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