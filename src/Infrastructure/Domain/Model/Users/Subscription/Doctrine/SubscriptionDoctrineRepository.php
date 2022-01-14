<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\Subscription\Doctrine;

//use App\Domain\Model\Identity\User\User;
use App\Domain\Model\Users\Subscription\Subscription;
use App\Domain\Model\Users\Subscription\SubscriptionRepository;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;

class SubscriptionDoctrineRepository extends AbstractDoctrineRepository implements SubscriptionRepository {

    protected string $entityClass = Subscription::class;
    
    function getPart(string $currentUserId, int $page, int $count) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb
            ->select('r')
            ->from($this->entityClass, 'r')
            ->where('r.initiatorId = :initiator_id AND r.secondId = :second_id')
            ->orWhere('r.initiatorId = :second_id AND r.secondId = :initiator_id')
            //->setParameter('initiator_id', $initiatorId)
            ->setParameters(array('initiator_id' => $initiatorId, 'second_id' => $secondId))
//            ->setMaxResults($limit) // по идее должен быть только один
            ->getQuery()
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')
            ->getResult();
    }
    
    function getByUsersIds(string $subscriberId, string $userId): ?Subscription {
//        $id = $this->createId($initiatorId, $secondId);
//        return $this->entityManager->find($this->entityClass, $id);
        /** @var \Doctrine\ORM\QueryBuilder $qb */
        $qb = $this->entityManager->createQueryBuilder();
        
        $qb->select('s')
            ->from(Subscription::class, 's')
            ->where('s.subscriberId = :subscriberId')
            ->andWhere('s.userId = :userId')
            ->setParameter('subscriberId', $subscriberId)
            ->setParameter('userId', $userId);
        
        $result = $qb->getQuery()->getOneOrNullResult();
        return $result;
    }

    public function getById(string $id): ?Subscription {
        return $this->entityManager->find($this->entityClass, $id);
    }

    public function getOfUser(string $userId, ?string $cursor, int $count): array {
        $qb = $this->entityManager->createQueryBuilder();
        
        $qb->select('s')
            ->from(Subscription::class, 's')
            ->where('s.subscriberId = :subscriberId');
        
        if($cursor) {
            $qb->andWhere('s.id >= :cursor')
               ->setParameter('cursor', $cursor);
        }

        $qb->setParameter('subscriberId', $userId)
            ->setMaxResults($count);
        
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    function getCountOfUser(string $userId): int {
        $qb = $this->entityManager->createQueryBuilder();
        
        $qb->select('count(s)')
            ->from(Subscription::class, 's')
            ->where('s.subscriberId = :subscriberId')
            ->setParameter('subscriberId', $userId);
        
        $result = $qb->getQuery()->getSingleScalarResult();
        return (int)$result;
    }
}
