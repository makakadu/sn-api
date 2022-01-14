<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Shares;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Photos\AlbumPhoto\AlbumPhoto;

class SharedUserAlbumPhoto extends Shared {
    
    private ?AlbumPhoto $photo;
    private ?User $creator;
            
    function __construct(AlbumPhoto $photo) {
        $this->creator = $photo->owner();
        $this->photo = $photo;
        $this->originalId = $photo->id();
        $this->createdAt = new \DateTime('now');
        $this->originalCreatedAt = $photo->createdAt();
    }

    public function acceptSharedVisitor(SharedVisitor $visitor) {
        return $visitor->visitSharedUserAlbumPhoto($this);
    }
    
    public function photo(): ?AlbumPhoto {
        return $this->photo;
    }

    public function creator(): ?User {
        return $this->creator;
    }

    public function shared(): ?Shareable {
        return $this->photo;
    }
}
