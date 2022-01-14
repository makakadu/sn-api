<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\SavesCollection\ProfileItems;

use App\Domain\Model\Users\SavesCollection\SavedItem;
use App\Domain\Model\Users\Photos\ProfilePicture\ProfilePicture;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\SavesCollection\SavesCollection;

class ProfilePictureItem extends SavedItem {

    /*
     * свойства $photo, $owningGroup и $creator являются nullable, потому что фото, группа и создатель могут быть удалены. $creator также nullable из-за того, что фото может быть
     * создано от имени группы
     */
    private ?ProfilePicture $picture;
    private ?User $user;
            
    function __construct(SavesCollection $collection, ProfilePicture $picture) {
        parent::__construct($collection, $picture->createdAt, 'photo');
        $this->originalId = $picture->id();
        $this->picture = $picture;
        $this->user = $picture->owner();
        $this->originalCreatedAt = $picture->createdAt();
    }

    public function saved(): ?\App\Domain\Model\Saveable {
        return $this->picture;
    }

    public function acceptItemVisitor(\App\Domain\Model\Users\SavesCollection\SavedItemVisitor $visitor) {
        return $visitor->visitProfilePictureItem($this);
    }
    
    public function picture(): ?ProfilePicture {
        return $this->picture;
    }

    public function owner(): ?User {
        return $this->user;
    }

    public function pictureId(): string {
        return $this->pictureId;
    }

    public function originalCreatedAt(): \DateTime {
        return $this->originalCreatedAt;
    }

}