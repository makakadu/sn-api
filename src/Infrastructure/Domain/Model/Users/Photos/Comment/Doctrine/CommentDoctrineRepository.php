<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\Photos\Comment\Doctrine;

use App\Domain\Model\Users\Photos\Comment\Comment;
use App\Domain\Model\Users\Photos\Comment\CommentRepository;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;

class CommentDoctrineRepository extends AbstractDoctrineRepository implements CommentRepository {
    protected string $entityClass = Comment::class;
    
    public function getPart(string $postId, ?string $offsetId, int $limit): array {
        
    }

    public function getById(string $id): ?Comment {
        return $this->entityManager->find($this->entityClass, $id);
    }

}
