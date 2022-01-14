<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\Connection\Doctrine;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Connection\Connection;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;

class ConnectionDoctrineRepository extends AbstractDoctrineRepository implements ConnectionRepository {

    protected string $entityClass = Connection::class;
    
    function getPartOld(string $currentUserId, int $page, int $count) {
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
    
//    public function getByUsersIds(int $initiatorId, int $secondId) {
//        $qb = $this->entityManager->createQueryBuilder();
//        return $qb
//            ->select('r')
//            ->from($this->entityClass, 'r')
//            ->where('r.initiatorId = :initiator_id AND r.secondId = :second_id')
//            ->orWhere('r.initiatorId = :second_id AND r.secondId = :initiator_id')
//            //->setParameter('initiator_id', $initiatorId)
//            ->setParameters(array('initiator_id' => $initiatorId, 'second_id' => $secondId))
////            ->setMaxResults($limit) // по идее должен быть только один
//            ->getQuery()
//            //->useQueryCache(true)
//            //->setResultCacheId('kek')
//            //->useResultCache(true, 3600, 'kek')
//            ->getSingleResult();
//    }
    
//    private function createId(string $initiatorId, string $secondId): string {
//        return $initiatorId < $secondId
//            ? "{$initiatorId}-{$secondId}" : "{$secondId}-{$initiatorId}";
//    }
    
    public function getByUsersIds(string $user1Id, string $user2Id): ?Connection {
//        $id = $this->createId($initiatorId, $secondId);
//        return $this->entityManager->find($this->entityClass, $id);
        /** @var \Doctrine\ORM\QueryBuilder $qb */
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('f')
            ->from(Connection::class, 'f')
            ->where('f.user1Id = :user1Id AND f.user2Id = :user2Id')
            ->orWhere('f.user1Id = :user2Id AND f.user2Id = :user1Id')
            ->setParameter('user1Id', (string)$user1Id)
            ->setParameter('user2Id', (string)$user2Id);
        
        $result = $qb->getQuery()->getOneOrNullResult();
        return $result;
    }
    
    function haveCommonFriend(string $user1Id, string $user2Id): bool {
        $friendsOfUser1 = $this->getFriendsOfUser($user1Id);
        $friendsOfUser2 = $this->getFriendsOfUser($user2Id);
        return (bool)array_intersect($friendsOfUser1, $friendsOfUser2);
        //return false;
//        print_r($friendsOfUser1);
//        print_r($friendsOfUser2);
//        exit();
    }
    
    public function getFriendsOfUser(string $userId, ?int $limit = null) {
        $qb = $this->entityManager->createQueryBuilder();
        
        /** @var \Doctrine\ORM\QueryBuilder $qb */
        $qb->select('f.user1.id, f.user2.id')
            ->from(Friendship::class, 'f')
            ->where('f.user1.id = :userId')
            ->orWhere('f.user2.id = :userId')
            ->andWhere('f.status = 2')
            ->setParameter(':userId', (string)$userId);
        
        if($limit) {
            $qb->setMaxResults($limit);
        }
        
        $friendships = $qb->getQuery()->getResult();
        
        $ids = [];
        foreach($friendships as $friendship) {
            if($friendship['user1.id'] === (string)$userId) {
                $ids[] = $friendship['user2.id'];
            } else {
                $ids[] = $friendship['user1.id'];
            }
        }
        
        return $ids;
    }
    
    /**
     * @return array<Connection>
     */
    function getWithUser(User $user, ?string $cursor, int $count, bool $hideAccepted, bool $hidePending, ?string $type): array { // Я не указывал нигде, но мне кажется, что если передан contactId, то offsetId не должно действовать
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('c')->from(Connection::class, 'c');
        
        if(!$type) {
            $qb->where('c.user1Id = :userId OR c.user2Id = :userId');
        }
        else {
            if($type === 'outgoing') {
                $qb->where('c.user1Id = :userId');
            }
            elseif($type === 'incoming') {
                $qb->where('c.user2Id = :userId');
            }
        }
        
        if($hideAccepted) {
            $qb->andWhere('c.isAccepted != 1');
        }
        if($hidePending) {
            $qb->andWhere('c.isAccepted != 0');
        }
        if($cursor) {
            $qb->andWhere('c.id >= :cursor')
            ->setParameter('cursor', $cursor);
        }
        
        $qb->setParameter('userId', $user->id())
            ->setMaxResults($count);
        
        $result = $qb->getQuery()->getResult();
        return $result;
    }
    
    public function getCountWithUser(User $user, bool $hideAccepted, bool $hidePending, ?string $type): int {
        $repository = $this->entityManager->getRepository(Connection::class);
        
        $qb = $repository->createQueryBuilder('c');
        $qb->select('count(c.id)');
        if(!$type) {
            $qb->where('c.user1Id = :userId OR c.user2Id = :userId');
        }
        else {
            if($type === 'outgoing') {
                $qb->where('c.user1Id = :userId');
            }
            elseif($type === 'incoming') {
                $qb->where('c.user2Id = :userId');
            }
        }
        if($hideAccepted) {
            $qb->andWhere('c.isAccepted != 1');
        }
        if($hidePending) {
            $qb->andWhere('c.isAccepted != 0');
        }
        
        $qb->setParameter('userId', $user->id());
        $query = $qb->getQuery();
        return (int)$query->getSingleScalarResult();
        
//        return (int)$repository->createQueryBuilder('c')
//            ->select('count(c.id)')
//            ->where('c.user1Id = :userId OR c.user2Id = :userId')
//            ->setParameter('userId', $user->id())
//            ->getQuery()
//            ->getSingleScalarResult();
    }
    
    public function getCountByUser(User $user): int {
        $repository = $this->entityManager->getRepository(Connection::class);
        return (int)$repository->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.user1Id = :userId OR c.user2Id = :userId')
            ->setParameter('userId', $user->id())
            ->getQuery()
            ->getSingleScalarResult();
    }
    
//    public function getFriendsIdsOf(string $userId) {
//        $qb = $this->entityManager->createQueryBuilder();
//        $result = $qb
//            ->select('r.requesterId', 'r.requesteeId')
//            ->from($this->entityClass, 'r')
//            ->where('r.requesterId = :user_id OR r.requesteeId = :user_id')
//            ->andWhere('r.status = 2')
//            ->setParameter('user_id', $userId)
//            //->setParameters(array('initiator_id' => $initiatorId, 'second_side_id' => $secondId))
//            ->getQuery()
//            //->useQueryCache(true)
//            //->setResultCacheId('kek')
//            //->useResultCache(true, 3600, 'kek')
//            ->getResult();
//        return array_map(fn($el) => $el['requesterId'] === $userId? $el['requesteeId'] : $el['requesterId'], $result);
//    }
//    
//    public function getFriendshipOffersOf(string $userId) {
//        $qb = $this->entityManager->createQueryBuilder();
//        $result = $qb
//            ->select('r.id', 'r.requesterId', 'r.requesteeId')
//            ->from($this->entityClass, 'r')
//            ->where('r.requesterId = :user_id OR r.requesteeId = :user_id')
//            ->andWhere('r.status = 1')
//            ->setParameter('user_id', $userId)
//            //->setParameters(array('initiator_id' => $initiatorId, 'second_side_id' => $secondId))
//            ->getQuery()
//            //->useQueryCache(true)
//            //->setResultCacheId('kek')
//            //->useResultCache(true, 3600, 'kek')
//            ->getResult();
//        return array_map(fn($el) => [
//            'id' => $el['id'], 'requesterId' => $el['requesterId'], 'requesteeId' => $el['requesteeId']
//        ], $result);
//    }
//
//    
//    public function getFriendsOf(int $userId) {
//        $qb = $this->entityManager->createQueryBuilder();
//        $result = $qb
//            ->select('u.id', 'u.firstName', 'u.lastName', 'u.wall')
//            ->from(\App\Domain\Model\Identity\User\User::class, 'u')
//            ->innerJoin($this->entityClass, 'f')
//            ->where('f.requesterId = :user_id AND f.requesteeId = u.id')
//            ->orWhere('f.requesterId = u.id AND f.requesteeId = :user_id')
//            ->andWhere('f.status = 2')
//            ->setParameter('user_id', $userId)
//            //->setParameters(array('initiator_id' => $initiatorId, 'second_side_id' => $secondId))
//            ->getQuery()
//            //->useQueryCache(true)
//            //->setResultCacheId('kek')
//            //->useResultCache(true, 3600, 'kek')
//            ->getResult();
//        foreach($result as $el) {
//            print_r($el['avatar']);
//            echo '<br>';
//        }
//        exit();
//        
//        return array_map(fn($el) => $el['requesterId'] === $userId? $el['requesteeId'] : $el['requesterId'], $result);
//    }
    
//    public function getByInitiatorId(int $id) {
//        
//    }
//    
//    public function getBySecondId(int $id) {
//        
//    }

//    function flush() {
//        try {
//            parent::flush();
//        } catch (UniqueConstraintViolationException $e) {
//            throw new NotUniqueValueException('Relationship with such ID already exist');
//        }
//    }

    public function getFriendsIdsOf(string $userId) {
        
    }

    public function getFriendshipOffersOf(string $userId) {
        
    }

    public function getById(string $id): ?Connection {
        return $this->entityManager->find($this->entityClass, $id);
    }

}
