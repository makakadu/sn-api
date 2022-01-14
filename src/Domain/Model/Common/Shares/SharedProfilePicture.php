<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Shares;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Photos\ProfilePicture\ProfilePicture;

class SharedProfilePicture extends Shared {
    
    private ?ProfilePicture $picture; // оригинал может быть удалён, поэтому стоит сделать это свойство nullable
    private ?User $user; // И группа тоже может быть удалена
            
    function __construct(ProfilePicture $picture) {
        $this->user = $picture->owner();
        $this->picture = $picture;
        $this->originalId = $picture->id();
        $this->createdAt = new \DateTime('now');
        $this->originalCreatedAt = $picture->createdAt();
    }

    public function acceptSharedVisitor(SharedVisitor $visitor) {
        return $visitor->visitSharedProfilePicture($this);
    }
    
    public function picture(): ?ProfilePicture {
        return $this->picture;
    }

    public function user(): ?User {
        return $this->user;
    }

    public function shared(): ?Shareable {
        return $this->picture;
    }

}
