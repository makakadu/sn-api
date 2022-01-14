<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Post\Photo;

use App\Domain\Repository;

interface PhotoRepository extends Repository {
    function getById(string $id): ?Photo;
}
