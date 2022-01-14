<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Pages\Post\SuggestedPost\Doctrine;

use App\Domain\Model\Pages\Post\SuggestedPost\SuggestedPost;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Pages\Post\SuggestedPost\SuggestedPostRepository;

class SuggestedPostDoctrineRepository extends AbstractDoctrineRepository implements SuggestedPostRepository {
    
    protected string $entityClass = SuggestedPost::class;
    
    public function getById(string $id): ?SuggestedPost {
        return $this->entityManager->find($this->entityClass, $id);
    }

}
