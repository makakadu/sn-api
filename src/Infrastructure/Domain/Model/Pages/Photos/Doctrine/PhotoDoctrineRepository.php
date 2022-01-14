<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Pages\Photos\Doctrine;

use App\Domain\Model\Pages\Photos\AlbumPhoto;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Pages\Photos\PhotoRepository;

class PhotoDoctrineRepository extends AbstractDoctrineRepository implements PhotoRepository {
    protected string $entityClass = AlbumPhoto::class;
    public function getById(string $id): ?AlbumPhoto {
        return $this->entityManager->find($this->entityClass, $id);
    }

}
