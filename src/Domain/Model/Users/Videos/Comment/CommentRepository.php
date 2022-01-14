<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Videos\Comment;

use App\Domain\Repository;
use App\Domain\Model\Users\Videos\Video;

interface CommentRepository extends Repository {
    /**
     * @return array<Video>
     */
    function getPart(string $postId, ?string $offsetId, int $limit): array;
}
