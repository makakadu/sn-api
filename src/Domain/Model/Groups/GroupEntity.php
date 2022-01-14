<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups;

use App\Domain\Model\Groups\Group\Group;

trait GroupEntity {
    
    protected Group $owningGroup;
    
    function owningGroup(): Group {
        return $this->owningGroup;
    }
}
