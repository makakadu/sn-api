<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\Comments\Doctrine;

use App\Domain\Model\Users\Comments\ProfileComment;
use App\Domain\Model\Users\Comments\ProfileCommentRepository;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;

class ProfileCommentDoctrineRepository extends AbstractDoctrineRepository implements ProfileCommentRepository {
    
    protected string $entityClass = ProfileComment::class;
    
    public function getById(string $id): ?ProfileComment {
        return $this->entityManager->find($this->entityClass, $id);
    }

}
