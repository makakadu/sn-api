<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Groups\Post\Doctrine;

use App\Domain\Model\Groups\Post\Post;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Groups\Post\PostRepository;

class PostDoctrineRepository extends AbstractDoctrineRepository implements PostRepository {
    protected string $entityClass = Post::class;
    
    public function getById(string $id): ?Post {
        return $this->entityManager->find($this->entityClass, $id);
    }

}
