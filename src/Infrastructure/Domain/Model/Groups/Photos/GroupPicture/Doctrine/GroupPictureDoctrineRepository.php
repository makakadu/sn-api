<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Groups\Photos\GroupPicture\Doctrine;

use App\Domain\Model\Groups\Photos\GroupPicture\GroupPicture;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Groups\Photos\GroupPicture\GroupPictureRepository;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use Doctrine\ORM\EntityManager;

class GroupPictureDoctrineRepository extends AbstractDoctrineRepository implements GroupPictureRepository {
    protected string $entityClass = GroupPicture::class;
    
    private ConnectionRepository $connections;
    
    function __construct(EntityManager $entityManager, ConnectionRepository $connections) {
        $this->connections = $connections;
        $this->entityManager = $entityManager;
    }

    public function getById(string $id): ?GroupPicture {
        return $this->entityManager->find($this->entityClass, $id);
    }


}
