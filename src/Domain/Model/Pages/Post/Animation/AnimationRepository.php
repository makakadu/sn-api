<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Post\Animation;

use App\Domain\Repository;

interface AnimationRepository extends Repository {
    function getById(string $id): ?Animation;
}
