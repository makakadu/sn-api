<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Shares;

use App\Domain\Model\Groups\Group\Group;
use App\Domain\Model\Groups\Photos\AlbumPhoto\AlbumPhoto;
use App\Domain\Model\Users\User\User;

class SharedGroupAlbumPhoto extends Shared {
    
    private ?AlbumPhoto $photo; // оригинал может быть удалён, поэтому стоит сделать это свойство nullable
    private ?Group $group; // И группа тоже может быть удалена
    private ?User $creator; // Если фото создано в группе от имени пользователя
    private bool $onBehalfOfGroup;
            
    function __construct(AlbumPhoto $photo) {
        /*
        elseif($original->onBehalfOfGroup()) {
            throw new DomainException("Cannot share photo from group created NOT on behalf of the group (created on behalf of user)");
        }
         */
        $this->group = $photo->owningGroup();
        $this->photo = $photo;
        $this->originalId = $photo->id();
        $this->creator = $photo->creator();
        $this->onBehalfOfGroup = $photo->onBehalfOfGroup();
        $this->createdAt = new \DateTime('now');
        $this->originalCreatedAt = $photo->createdAt();
    }

    public function acceptSharedVisitor(SharedVisitor $visitor) {
        return $visitor->visitSharedGroupAlbumPhoto($this);
    }
    
    public function photo(): ?AlbumPhoto {
        return $this->photo;
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
        return $this->photo;
    }

}
