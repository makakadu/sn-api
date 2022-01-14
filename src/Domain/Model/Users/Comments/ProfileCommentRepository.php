<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Comments;

interface ProfileCommentRepository {
    function getById(string $id): ?ProfileComment;
}
