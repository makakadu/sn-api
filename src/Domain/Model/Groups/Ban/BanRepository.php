<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Ban;

use App\Domain\Repository;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Group\Group;

interface BanRepository extends Repository {

    function getById(string $id): ?Ban;
    
    function getByGroupAndUser(Group $group, User $user): ?Ban;
}
