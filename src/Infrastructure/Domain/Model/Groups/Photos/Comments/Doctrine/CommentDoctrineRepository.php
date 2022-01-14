<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Groups\Photos\Comments\Doctrine;

use App\Domain\Model\Groups\Photos\Comments\Comment;
use App\Domain\Model\Groups\Photos\Comments\CommentRepository;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;

class CommentDoctrineRepository extends AbstractDoctrineRepository implements CommentRepository {
    protected string $entityClass = Comment::class;
    
    public function getPart(string $postId, ?string $offsetId, int $limit): array {
        return $this->entityManager->find($this->entityClass, $id);
    }

}
