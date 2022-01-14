<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Photos;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Page\Page;

class Reaction extends \App\Domain\Model\Users\ProfileReaction {
    
    private Photo $photo;
    
    function __construct(User $creator, Photo $photo, int $type) {//, ?Page $asPage) {
        if(!in_array($type, $this->reactionsTypes())) {
            throw new \InvalidArgumentException("Value of type should be from 1 to 8");
        }
        $this->id = (string)\Ulid\Ulid::generate(true);
        $this->creator = $creator;
        $this->owner = $photo->owner();
        $this->photo = $photo;
        
        //$this->asPage = $asPage;
        //$this->pageId = $asPage ? $asPage->id() : "";
        
        $this->reactionType = $type;
        $this->createdAt = new \DateTime('now');
    }
    
    function photo(): Photo {
        return $this->photo;
    }

    function owner(): User {
        return $this->owner;
    }

    public function reacted(): \App\Domain\Model\Common\Reactable {
        return $this->photo;
    }

}