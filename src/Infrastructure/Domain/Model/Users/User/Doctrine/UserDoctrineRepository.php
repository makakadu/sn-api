<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\User\Doctrine;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\ConflictException;
use App\Domain\Model\Users\User\UserRepository;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Domain\Model\Relationship\Relationship;
use App\Domain\Model\Users\Friendship\Friendship;
use Doctrine\ORM\QueryBuilder;
use App\Infrastructure\Domain\Model\Users\Friendship\Doctrine\FriendshipDoctrineRepository;

class UserDoctrineRepository extends AbstractDoctrineRepository implements UserRepository {

    protected string $entityClass = User::class;

    function getByFirstName(string $firstName) {
//        $qb = $this->entityManager->createQueryBuilder();
//        $result = $qb
//            ->select('u')
//            ->from(User::class, 'u')
//            ->where('u.first_name = :name')
//            ->setParameter(':name', $firstName)
//            ->getQuery()
//            //->useQueryCache(true)
//            //->setResultCacheId('kek')
//            //->useResultCache(true, 3600, 'kek')
//            ->getResult();
//        return $result;
    }
    
    public function getByUsername(string $username): ?User{
//        return $this->entityManager->getRepository($this->entityClass)
//            ->findBy(array('username.username' => $username));
        $qb = $this->entityManager->createQueryBuilder();
        $result = $qb
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.username.username LIKE :username')
            ->setParameter(':username', $username)
            ->getQuery()
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')
            ->getOneOrNullResult();
        return $result;
    }
    
    function search(User $requester, string $text, ?string $cursor, int $count) {

        $exploded = \explode(' ', $text);
        if(\count($exploded) > 1) {
            $firstWord = $exploded[0];
            $secondWord = $exploded[1];
            
            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('u')
                ->from(User::class, 'u')
                ->where("u.firstName LIKE '%{$firstWord}%' AND u.lastName LIKE '%{$secondWord}%'")
                ->orWhere("u.firstName LIKE '%{$secondWord}%' AND u.lastName LIKE '%{$firstWord}%'")
                ->andWhere("u.id != '{$requester->id()}'");
            if($cursor) {
                $qb->andWhere("u.id >= '{$cursor}'");
            }
            return $qb->setMaxResults($count)
                ->getQuery()
                //->useQueryCache(true)
                //->setResultCacheId('kek')
                //->useResultCache(true, 3600, 'kek')
                ->getResult();
        } else {            
            $qb = $this->entityManager->createQueryBuilder();
            $qb
                ->select('u')
                ->from(User::class, 'u')
                ->where("u.firstName LIKE '%{$text}%'")
                ->orWhere("u.lastName LIKE '%{$text}%'")
                ->andWhere("u.id != '{$requester->id()}'");
            if($cursor) {
                $qb->andWhere("u.id >= '{$cursor}'");
            }
            
            $query = $qb->setMaxResults($count)
                ->getQuery();
            
            return $query->getResult();
        }
    }
    
    function getByUsername2(string $username) {
        return $this->entityManager->getRepository($this->entityClass)
            ->findBy(array('username.username' => $username)); // Поиск по embeddable (https://github.com/laravel-doctrine/fluent/issues/51)
    }
    
    function getCommonContacts(User $user, User $commonWith, ?string $cursor, int $count) {
        $qb1 = $this->entityManager->createQueryBuilder();
        
        $result1 = $qb1
            ->select('c.user1Id, c.user2Id')
            ->from(\App\Domain\Model\Users\Connection\Connection::class, 'c')
            ->where('c.user1Id = :user_id OR c.user2Id = :user_id')
            ->andWhere('c.isAccepted = 1')
            ->setParameter('user_id', $user->id())
            ->getQuery()
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')
            ->getResult();
        
        $contacts1Ids = [];
        foreach($result1 as $item) {
            $contacts1Ids[] = $item['user1Id'] === $user->id()
                ? $item['user2Id'] : $item['user1Id'];
        }
        
        $qb2 = $this->entityManager->createQueryBuilder();
        $result2 = $qb2
            ->select('c.user1Id, c.user2Id')
            ->from(\App\Domain\Model\Users\Connection\Connection::class, 'c')
            ->where('c.user1Id = :user_id OR c.user2Id = :user_id')
            ->andWhere('c.isAccepted = 1')
            ->setParameter('user_id', $commonWith->id())
            ->getQuery()
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')
            ->getResult();
        
        $contacts2Ids = [];
        foreach($result2 as $item) {
            $contacts2Ids[] = $item['user1Id'] === $commonWith->id()
                ? $item['user2Id'] : $item['user1Id'];
        }
        
        $common = \array_intersect($contacts1Ids, $contacts2Ids);
        return $this->getUsersByIds($common, $cursor, $count);
    }
    
    function getCommonContactsCount(User $user, User $commonWith) {
        $qb1 = $this->entityManager->createQueryBuilder();
        
        $result1 = $qb1
            ->select('c.user1Id, c.user2Id')
            ->from(\App\Domain\Model\Users\Connection\Connection::class, 'c')
            ->where('c.user1Id = :user_id OR c.user2Id = :user_id')
            ->andWhere('c.isAccepted = 1')
            ->setParameter('user_id', $user->id())
            ->getQuery()
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')
            ->getResult();
        
        $contacts1Ids = [];
        foreach($result1 as $item) {
            $contacts1Ids[] = $item['user1Id'] === $user->id()
                ? $item['user2Id'] : $item['user1Id'];
        }
        
        $qb2 = $this->entityManager->createQueryBuilder();
        $result2 = $qb2
            ->select('c.user1Id, c.user2Id')
            ->from(\App\Domain\Model\Users\Connection\Connection::class, 'c')
            ->where('c.user1Id = :user_id OR c.user2Id = :user_id')
            ->andWhere('c.isAccepted = 1')
            ->setParameter('user_id', $commonWith->id())
            ->getQuery()
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')
            ->getResult();
        
        $contacts2Ids = [];
        foreach($result2 as $item) {
            $contacts2Ids[] = $item['user1Id'] === $commonWith->id()
                ? $item['user2Id'] : $item['user1Id'];
        }
        
        return count(\array_intersect($contacts1Ids, $contacts2Ids));
    }
    
    function getUserContacts(User $user, ?string $cursor, int $count) {
        $qb = $this->entityManager->createQueryBuilder();
        $result = $qb
            ->select('c.user1Id, c.user2Id')
            ->from(\App\Domain\Model\Users\Connection\Connection::class, 'c')
            ->where('c.user1Id = :user_id OR c.user2Id = :user_id')
            ->andWhere('c.isAccepted = 1')
            ->setParameter('user_id', $user->id())
            ->getQuery()
            ->getResult();
        $contactsIds = [];
        foreach($result as $item) {
            $contactsIds[] = $item['user1Id'] === $user->id()
                ? $item['user2Id'] : $item['user1Id'];
        }
        return $this->getUsersByIds($contactsIds, $cursor, $count);
    }
    
    function getUserSubscriptions(User $user, ?string $cursor, int $count) {
        $qb = $this->entityManager->createQueryBuilder();
        $result = $qb
            ->select('s.userId')
            ->from(\App\Domain\Model\Users\Subscription\Subscription::class, 's')
            ->where('s.subscriberId = :user_id')
            ->setParameter('user_id', $user->id())
            ->getQuery()
            ->getResult();
        $ids = [];
        foreach($result as $item) {
            $ids[] = $item['userId'];
        }
        return $this->getUsersByIds($ids, $cursor, $count);
    }
    
    function getUserSubscribers(User $user, ?string $cursor, int $count) {
        $qb = $this->entityManager->createQueryBuilder();
        $result = $qb
            ->select('s.subscriberId')
            ->from(\App\Domain\Model\Users\Subscription\Subscription::class, 's')
            ->where('s.userId = :user_id')
            ->setParameter('user_id', $user->id())
            ->getQuery()
            ->getResult();
        $ids = [];
        foreach($result as $item) {
            $ids[] = $item['subscriberId'];
        }
        return $this->getUsersByIds($ids, $cursor, $count);
    }
    
    function getSubscribersCount(User $user) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb
            ->select('count(s.id)')
            ->from(\App\Domain\Model\Users\Subscription\Subscription::class, 's')
            ->where('s.userId = :user_id')
            ->setParameter('user_id', $user->id())
            ->getQuery()
            ->getSingleScalarResult();
    }
    
    function getUserContactsCount(User $user) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb
            ->select('count(c.id)')
            ->from(\App\Domain\Model\Users\Connection\Connection::class, 'c')
            ->where('c.user1Id = :user_id OR c.user2Id = :user_id')
            ->andWhere('c.isAccepted = 1')
            ->setParameter('user_id', $user->id())
            ->getQuery()
            ->getSingleScalarResult();
    }
    
    function getSubscriptionsCount(User $user) {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb
            ->select('count(s.id)')
            ->from(\App\Domain\Model\Users\Subscription\Subscription::class, 's')
            ->where('s.subscriberId = :user_id')
            ->setParameter('user_id', $user->id())
            ->getQuery()
            ->getSingleScalarResult();
    }
    
    public function getUsersByIds(array $ids, ?string $cursor, int $count) {
        $qb = $this->entityManager->createQueryBuilder();
        $query = $qb
            ->select('u')
            ->from(User::class, 'u')
            ->where($qb->expr()->in('u.id', ':data'));
        
        if($cursor) {
            $qb->andWhere('u.id >= :cursor')
               ->setParameter('cursor', $cursor);
        }
        return $qb->setParameter('data', $ids) 
            ->setMaxResults($count)
            //->setParameters(array('initiator_id' => $initiatorId, 'second_side_id' => $secondId))
            ->getQuery()
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')
            ->getResult();
    }
    
    public function getOnlineFriendsByIds(array $ids) {
        $ids = \implode(',', $ids);
        $sql = "SELECT id FROM users WHERE is_online = true " . (!empty($ids) ? "AND id IN($ids) " : "") . "ORDER BY rand() LIMIT 3";
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();
        
        $ids = array_map(fn($el) => $el['id'], $stmt->fetchAll());
        
        return $this->entityManager->getRepository($this->entityClass)->findBy(['id' => $ids]);
//        $qb = $this->entityManager->createQueryBuilder();
//        return $qb
//            ->select('u')
//            ->from(\App\Domain\Model\Identity\User\User::class, 'u')
//            ->andWhere($qb->expr()->in('u.id', ':ids'))
//            ->setParameter('ids', $ids)
//            //->setParameters(array('initiator_id' => $initiatorId, 'second_side_id' => $secondId))
//            ->getQuery()
//            //->useQueryCache(true)
//            //->setResultCacheId('kek')
//            //->useResultCache(true, 3600, 'kek')
//            ->getResult();
    }
    
//    function getById2($id) {
//        /** @var User $user */
//        $id = 2;
//        $user = $this->entityManager->find($this->entityClass, $id);
//        if($user) {
////            $friends = "SELECT initiator_id, second_side_id FROM relationships WHERE status = 3 AND (initiator_id = '{$user->id()}' OR second_side_id = '{$user->id()}')";
////            $stmt = $this->entityManager->getConnection()->prepare($friends);
////            $stmt->execute();
////            $user->setFriendsIds(array_map(fn($el) => (int)$el['initiator_id'] === $user->id() ? $el['second_side_id'] : $el['initiator_id'], $stmt->fetchAll()));
//            
//            $banned = "SELECT initiator_id, second_side_id, status FROM relationships WHERE status IN (0, 1) AND (initiator_id = '{$user->id()}' OR second_side_id = '{$user->id()}')";
//            $stmt = $this->entityManager->getConnection()->prepare($banned);
//            $stmt->execute();
//            $arr = array_map(function($el) {
//                if($el[status])
//            }, $stmt->fetchAll());
//            print_r($arr);exit();
//            $user->setBannedIds(array_map(fn($el) => (int)$el['initiator_id'] === $user->id() ? $el['second_side_id'] : $el['initiator_id'], $stmt->fetchAll()));
//        }
//        return $user;
//    }

    function getByEmail(string $email): ?User {
        /** @var Doctrine\ORM\QueryBuilder $qb */
        $qb = $this->entityManager->createQueryBuilder();
        $result = $qb
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.email = :email')
            ->setParameter(':email', $email)
            ->getQuery()
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')
            ->getOneOrNullResult();
        return $result;
    }
    
    function getRandomUsers(array $ids, int $limit) {
        $idsList = implode(',', $ids);
        //$sql = "SELECT id, first_name, last_name FROM users u INNER JOIN avatars a ON(u.id = a.owner_id) WHERE id IN($idsList) ORDER BY rand() LIMIT $limit";
        $sql = "SELECT id, first_name, last_name FROM users WHERE id IN($idsList) ORDER BY rand() LIMIT 2";
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();
        print_r($stmt->fetchAll());exit();
        return array_map(fn($el) => $el['second_id'], $stmt->fetchAll());
    }
    
    function getPartOld(int $currentUserId, int $page, int $count) {
        $limit = $count;
        $offset = $page * $count - $count;

//        return $this->entityManager->getRepository($this->entityClass)
//            ->findBy(
//                array(),
//                array(),
//                $limit,
//                $offset
//            );
        
        $qb = $this->entityManager->createQueryBuilder();
        return $qb
            ->select('u')
            ->from($this->entityClass, 'u')
            ->where('u.id != :requester_id')
            ->setParameter(':requester_id', $currentUserId)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
//            ->useQueryCache(true)
//            ->setResultCacheId('kek')
//            ->useResultCache(true, 3600, 'kek')
            ->getResult();
    }
    
    function getById($id): ?User {
        return $this->entityManager->find($this->entityClass, $id);
        
        //$friends = new FriendshipDoctrineRepository($this->entityManager);
        
        //$ids = $friends->getFriendsIdsOf($id);
        //$user->setFriendsIds($ids);
        
        //$allOffers = $friends->getFriendshipOffersOf($id);
        //print_r($allOffers);exit();
//        
//        $user->setReceivedFriendshipRequests(
//            \array_map(
//                fn($el) => $el['id'],
//                \array_filter($allOffers, fn($el) => $el['requesteeId'] === $user->id())
//            )
//        );
//        $user->setSentFriendshipRequests(
//            \array_map(
//                fn($el) => $el['id'],
//                \array_filter($allOffers, fn($el) => $el['requesterId'] === $user->id())
//            )
//        );
        
        //return $user;
    }

    function flush(): void {
        try {
            parent::flush();
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            throw new \App\Application\Exceptions\UniqueConstraintViolationException($e->getMessage());
        }
    }

}
