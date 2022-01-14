<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\Photos\ProfilePicture\Doctrine;

use App\Domain\Model\Users\Photos\ProfilePicture\ProfilePicture;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Users\Photos\ProfilePicture\ProfilePictureRepository;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use Doctrine\ORM\EntityManager;

class ProfilePictureDoctrineRepository extends AbstractDoctrineRepository implements ProfilePictureRepository {
    protected string $entityClass = ProfilePicture::class;
    
    private ConnectionRepository $connections;
    
    function __construct(EntityManager $entityManager, ConnectionRepository $connections) {
        $this->connections = $connections;
        $this->entityManager = $entityManager;
    }

    public function getById(string $id): ?ProfilePicture {
        return $this->entityManager->find($this->entityClass, $id);
    }


}
