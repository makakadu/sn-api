<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Post\Video;

use App\Domain\Repository;

interface VideoRepository extends Repository {
    function getById(string $id): ?Video;
}
