<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\SavesCollection\GroupItems;

use App\Domain\Model\Users\SavesCollection\SavedItem;
use App\Domain\Model\Groups\Photos\AlbumPhoto\AlbumPhoto;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Group\Group;
use App\Domain\Model\Users\SavesCollection\SavesCollection;

class GroupAlbumPhotoItem extends SavedItem {

    /*
     * свойства $photo, $owningGroup и $creator являются nullable, потому что фото, группа и создатель могут быть удалены. $creator также nullable из-за того, что фото может быть
     * создано от имени группы
     */
    private ?AlbumPhoto $photo;
    private ?Group $group;
    private bool $onBehalfOfGroup;
    private ?User $creator;
            
    function __construct(SavesCollection $collection, AlbumPhoto $photo) {
        parent::__construct($collection, $photo->createdAt, 'photo');
        $this->originalId = $photo->id();
        $this->photo = $photo;
        $this->creator = $photo->creator();
        $this->group = $photo->owningGroup();
        $this->onBehalfOfGroup = $photo->onBehalfOfGroup();
        $this->originalCreatedAt = $photo->createdAt();
    }

    public function saved(): ?\App\Domain\Model\Saveable {
        return $this->photo;
    }

    public function acceptItemVisitor(\App\Domain\Model\Users\SavesCollection\SavedItemVisitor $visitor) {
        return $visitor->visitGroupAlbumPhotoItem($this);
    }
    
    public function photo(): ?AlbumPhoto {
        return $this->photo;
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

    public function photoId(): string {
        return $this->photoId;
    }

    public function originalCreatedAt(): \DateTime {
        return $this->originalCreatedAt;
    }



}