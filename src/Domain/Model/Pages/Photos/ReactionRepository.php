<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Photos;

use App\Domain\Repository;

interface ReactionRepository extends Repository {
    function getById(string $id): ?Reaction;
}
