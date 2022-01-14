<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Common\Doctrine;

use App\Domain\Model\Common\PhotoInterface;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Common\PhotoRepository;
use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use Doctrine\ORM\EntityManager;

class PhotoDoctrineRepository extends AbstractDoctrineRepository implements PhotoRepository {
    protected string $entityClass = Photo::class;
    
    private ConnectionRepository $connections;
    
    function __construct(EntityManager $entityManager, ConnectionRepository $connections) {
        $this->connections = $connections;
        $this->entityManager = $entityManager;
    }

    public function getById(string $id): ?PhotoInterface {
        
    }

//    public function getById(string $id): ?PhotoInterface {
//        return new class implements PhotoInterface {
//            function accept(\App\Domain\Model\Common\PhotoVisitor $visitor) {
//                
//            }
//        };
//    }
}
