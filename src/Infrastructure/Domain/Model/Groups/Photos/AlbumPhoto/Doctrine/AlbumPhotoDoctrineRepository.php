<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Groups\Photos\AlbumPhoto\Doctrine;

use App\Domain\Model\Groups\Photos\AlbumPhoto\AlbumPhoto;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Groups\Photos\AlbumPhoto\AlbumPhotoRepository;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use Doctrine\ORM\EntityManager;

class AlbumPhotoDoctrineRepository extends AbstractDoctrineRepository implements AlbumPhotoRepository {
    protected string $entityClass = AlbumPhoto::class;
    
    private ConnectionRepository $connections;
    
    function __construct(EntityManager $entityManager, ConnectionRepository $connections) {
        $this->connections = $connections;
        $this->entityManager = $entityManager;
    }

    public function getById(string $id): ?AlbumPhoto {
        return $this->entityManager->find($this->entityClass, $id);
    }

}
