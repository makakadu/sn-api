<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Pages\Photos\PagePicture\Doctrine;

use App\Domain\Model\Pages\Photos\PagePicture\PagePicture;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Pages\Photos\PagePicture\PagePictureRepository;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use Doctrine\ORM\EntityManager;

class PagePictureDoctrineRepository extends AbstractDoctrineRepository implements PagePictureRepository {
    protected string $entityClass = PagePicture::class;
    
    private ConnectionRepository $connections;
    
    function __construct(EntityManager $entityManager, ConnectionRepository $connections) {
        $this->connections = $connections;
        $this->entityManager = $entityManager;
    }

    public function getById(string $id): ?PagePicture {
        return $this->entityManager->find($this->entityClass, $id);
    }


}
