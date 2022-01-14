<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\Post\Photo\Doctrine;

use App\Domain\Model\Users\Post\Photo\Photo;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Users\Post\Photo\PhotoRepository;

class PhotoDoctrineRepository extends AbstractDoctrineRepository implements PhotoRepository {
    protected string $entityClass = Photo::class;

    public function getById(string $id): ?Photo {
        return $this->entityManager->find($this->entityClass, $id);
    }

}
