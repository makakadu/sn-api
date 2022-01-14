<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\Post\Doctrine;

use App\Domain\Model\Users\Post\Comment\PostComment;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Users\Post\Comment\CommentRepository;

class CommentDoctrineRepository extends AbstractDoctrineRepository implements CommentRepository {
    protected $entityClass = PostComment::class;
}
