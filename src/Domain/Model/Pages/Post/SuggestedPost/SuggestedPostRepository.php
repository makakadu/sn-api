<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Post\SuggestedPost;

use App\Domain\Repository;

interface SuggestedPostRepository extends Repository {
    function getById(string $id): ?SuggestedPost;
}
