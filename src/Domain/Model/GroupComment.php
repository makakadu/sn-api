<?php
declare(strict_types=1);
namespace App\Domain\Model;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Group\Group;

interface GroupComment {
    function id(): string;
    function creator(): User;
    function owningGroup(): Group;
}
