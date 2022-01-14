<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\SavesCollection\GroupItems;

use App\Domain\Model\Users\SavesCollection\SavedItem;
use App\Domain\Model\Groups\Photos\GroupPicture\GroupPicture;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Group\Group;
use App\Domain\Model\Users\SavesCollection\SavesCollection;

class GroupPictureItem extends SavedItem {

    private ?GroupPicture $picture;
    private ?Group $group;
            
    function __construct(SavesCollection $collection, GroupPicture $picture) {
        parent::__construct($collection, $picture->createdAt, 'photo');
        $this->originalId = $picture->id();
        $this->picture = $picture;
        $this->group = $picture->owningGroup();
        $this->originalCreatedAt = $picture->createdAt();
    }

    public function saved(): ?\App\Domain\Model\Saveable {
        return $this->picture;
    }

    public function acceptItemVisitor(\App\Domain\Model\Users\SavesCollection\SavedItemVisitor $visitor) {
        return $visitor->visitGroupPictureItem($this);
    }
    
    public function photo(): ?GroupPicture {
        return $this->picture;
    }

    public function owningGroup(): ?Group {
        return $this->group;
    }

    public function pictureId(): string {
        return $this->pictureId;
    }

    public function originalCreatedAt(): \DateTime {
        return $this->originalCreatedAt;
    }

}