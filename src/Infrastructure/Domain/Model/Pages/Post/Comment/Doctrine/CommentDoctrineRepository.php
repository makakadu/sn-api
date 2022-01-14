<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Pages\Post\Comment\Doctrine;

use App\Domain\Model\Pages\Post\Comment\Comment;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Pages\Post\Comment\CommentRepository;

class CommentDoctrineRepository extends AbstractDoctrineRepository implements CommentRepository {
    protected string $entityClass = Comment::class;
    
    public function getById(string $id): ?Comment {
        return $this->entityManager->find($this->entityClass, $id);
    }
    
    /** @return array<Comment> */
    public function getPart(string $postId, ?string $offsetId, int $limit): array {
        
    }

}
