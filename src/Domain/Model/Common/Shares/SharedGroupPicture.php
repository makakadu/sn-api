<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Shares;

use App\Domain\Model\Groups\Group\Group;
use App\Domain\Model\Groups\Photos\GroupPicture\GroupPicture;

class SharedGroupPicture extends Shared {
    
    private ?GroupPicture $picture; // оригинал может быть удалён, поэтому стоит сделать это свойство nullable
    private ?Group $group; // И группа тоже может быть удалена
            
    function __construct(GroupPicture $picture) {
        $this->group = $picture->owningGroup();
        $this->picture = $picture;
        $this->originalId = $picture->id();
        $this->createdAt = new \DateTime('now');
        $this->originalCreatedAt = $picture->createdAt();
    }

    public function acceptSharedVisitor(SharedVisitor $visitor) {
        return $visitor->visitSharedGroupPicture($this);
    }
    
    public function picture(): ?GroupPicture {
        return $this->picture;
    }

    public function group(): ?Group {
        return $this->group;
    }

    public function shared(): ?Shareable {
        return $this->picture;
    }
}
