<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Membership;

use App\Domain\Repository;

interface MembershipRepository extends Repository {
    function getById(string $id): ?Membership;
}
