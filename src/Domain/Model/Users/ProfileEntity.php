<?php
declare(strict_types=1);
namespace App\Domain\Model\Users;

use App\Domain\Model\Users\User\User;

trait ProfileEntity {
    
    protected User $owner;
    protected User $creator;
    
    function owner(): User {
        return $this->owner;
    }
    
    function creator(): User {
        return $this->creator;
    }
}
