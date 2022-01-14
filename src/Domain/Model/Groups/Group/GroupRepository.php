<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Group;

use App\Domain\Repository;

interface GroupRepository extends Repository {
    function getById(string $id): ?Group;
    public function getByName(string $name): ?Group;
}
