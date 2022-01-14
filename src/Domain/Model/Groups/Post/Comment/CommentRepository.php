<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Post\Comment;

use App\Domain\Repository;

interface CommentRepository extends Repository {
    function getById(string $id): ?Comment;
    /** @return array<Comment> */
    function getPart(string $postId, ?string $offsetId, int $limit): array;
}
