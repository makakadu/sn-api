<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\ConnectionsList\Doctrine;

use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Users\ConnectionsList\ConnectionsListRepository;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\ConnectionsList\ConnectionsList;

class ConnectionsListDoctrineRepository extends AbstractDoctrineRepository implements ConnectionsListRepository {
    protected string $entityClass = ConnectionsList::class;
    
    public function getByOwnerId(string $ownerId) {
        $qb = $this->entityManager->createQueryBuilder();
        $result = $qb
            ->select('o')
            ->from($this->entityClass, 'o')
            ->where('o.owner_id = :owner_id')
            ->setParameter(':owner_id', $ownerId)
            ->getQuery()
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')
            ->getOneOrNullResult();
        return $result;
    }

    public function getById(string $id): ?ConnectionsList {
        return $this->entityManager->find($this->entityClass, $id);
    }

//    public function getByIds(array $ids) {
//        $repository = $this->entityManager->getRepository($this->entityClass);
//        return $repository->findBy(array('id' => $ids));
//    }
}