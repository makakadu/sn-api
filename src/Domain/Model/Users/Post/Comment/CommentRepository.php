<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Post\Comment;

use App\Domain\Repository;
use App\Domain\Model\Users\Post\Post;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Post\Comment\Comment;

interface CommentRepository extends Repository {
    function getById(string $id): ?Comment;
    
    function getPartOfActiveByPost(Post $post, ?string $offsetId, int $count, string $type, string $order): array;
    
    function getCountOfActiveByPost(Post $post): int;
    
    function getPartOfActiveByRootComment(Comment $comment, ?string $offsetId, int $count): array;
    
    function getCountOfActiveByRootComment(Comment $comment): int;
}
