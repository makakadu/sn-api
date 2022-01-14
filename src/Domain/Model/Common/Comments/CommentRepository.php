<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Comments;

use App\Domain\Repository;

interface CommentRepository extends Repository {
    function getById(string $id): ?Comment;
}
