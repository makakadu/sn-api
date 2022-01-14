<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Photos;

use App\Domain\Model\Users\User\User;

class Reaction extends \App\Domain\Model\Groups\GroupReaction {
    
    private Photo $photo;
    
    function __construct(User $creator, Photo $photo, string $type, bool $asGroup) {
        parent::__construct($creator, $photo->owningGroup(), $type, $asGroup);
        $this->photo = $photo;
    }
    
    function photo(): Photo {
        return $this->photo;
    }

    public function reacted(): \App\Domain\Model\Common\Reactable {
        return $this->photo;
    }

}