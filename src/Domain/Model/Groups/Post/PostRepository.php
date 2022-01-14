<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Post;

use App\Domain\Repository;

interface PostRepository extends Repository {
    function getById(string $id): ?Post;
}
