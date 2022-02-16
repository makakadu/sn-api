<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Chats\Doctrine;

use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Chats\Chat;
use App\Domain\Model\Chats\ChatRepository;

class ChatDoctrineRepository extends AbstractDoctrineRepository implements ChatRepository {
    protected string $entityClass = Chat::class;
    
    public function getById(string $id): ?Chat {
        return $this->entityManager->find($this->entityClass, $id);
    }

//    public function getPartOfUser(User $user, ?string $interlocutorId, ?string $cursor, int $count, string $order): array {
//        
//        $qb2 = $this->entityManager->createQueryBuilder();
//        $qb2->select('u.id')
//            ->from($this->entityClass, 'chat')
//            ->leftJoin('chat.participants', 'u')
//            ->where("chat.id = c.id");
//        
//        $qb = $this->entityManager->createQueryBuilder();
//        $qb
//            ->select('c')
//            ->from($this->entityClass, 'c')
//            ->where($qb->expr()->in(':user_id', $qb2->getDQL()));
//        if($cursor) {
//            $qb->andWhere('c.id > :cursor');
//            $qb->setParameters(array('cursor' => $cursor));
//        }
//        $qb->setParameter('user_id', $user->id());
//            
//        return $qb->setMaxResults($count)
//            ->getQuery()
//            //->useQueryCache(true)
//            //->setResultCacheId('kek')
//            //->useResultCache(true, 3600, 'kek')
//            ->getResult();
//    }
//    
//    public function getPartOfUser3(User $user, ?string $interlocutorId, ?string $type): array {
//        $qb = $this->entityManager->createQueryBuilder();
//        $qb->select('c')->from($this->entityClass, 'c')
//           ->leftJoin('c.participants', 'u')
//        
//        $qb2 = $this->entityManager->createQueryBuilder();
//        $qb2->select('u.id')
//            ->from($this->entityClass, 'chat')
//            ->leftJoin('chat.participants', 'p')
//            ->leftJoin('p.user', 'u')
//            ->where("chat.id = c.id");
//        
//        $qb3 = $this->entityManager->createQueryBuilder();
//        $qb3->select('u1.id')
//            ->from($this->entityClass, 'chat1')
//            ->leftJoin('chat1.participants', 'p1')
//            ->leftJoin('p1.user', 'u1')
//            ->where("chat1.id = c.id");
//        
//        if($interlocutorId) {            
//            $qb->where($qb->expr()->in(':user_id', $qb2->getDQL()));
//            $qb->andWhere($qb->expr()->in(':interlocutor_id', $qb3->getDQL()));
//            $qb->setParameter('interlocutor_id', $interlocutorId);
//        } else {
//            $qb->where($qb->expr()->in(':user_id', $qb2->getDQL()));
//        }
//        
//        if($type) {
//            $qb->andWhere('c.type = :type');
//            $qb->setParameter('type', $type);
//        }
//        $qb->setParameter('user_id', $user->id());
//            
//        $res = $qb->getQuery()
//            //->useQueryCache(true)
//            //->setResultCacheId('kek')
//            //->useResultCache(true, 3600, 'kek')
//            ->getResult();
////        echo count($res);exit();
//        return $res;
//    }
    
    public function getPartOfUser2(User $user, ?string $interlocutorId, ?string $type): array {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('c')->from($this->entityClass, 'c');
        
        $qb2 = $this->entityManager->createQueryBuilder();
        $qb2->select('u.id')
            ->from($this->entityClass, 'chat')
            ->leftJoin('chat.participants', 'p')
            ->leftJoin('p.user', 'u')
            ->where("chat.id = c.id");
        
        $qb3 = $this->entityManager->createQueryBuilder();
        $qb3->select('u1.id')
            ->from($this->entityClass, 'chat1')
            ->leftJoin('chat1.participants', 'p1')
            ->leftJoin('p1.user', 'u1')
            ->where("chat1.id = c.id");
        
        if($interlocutorId) {            
            $qb->where($qb->expr()->in(':user_id', $qb2->getDQL()));
            $qb->andWhere($qb->expr()->in(':interlocutor_id', $qb3->getDQL()));
            $qb->setParameter('interlocutor_id', $interlocutorId);
        } else {
            $qb->where($qb->expr()->in(':user_id', $qb2->getDQL()));
        }
        
        if($type) {
            $qb->andWhere('c.type = :type');
            $qb->setParameter('type', $type);
        }
        $qb->setParameter('user_id', $user->id());
            
        $res = $qb->getQuery()
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')
            ->getResult();
//        echo count($res);exit();
        return $res;
    }
    
    public function getPartOfUser(User $user, ?string $interlocutorId, ?string $cursor, int $count, ?string $type): array {

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('c')->from($this->entityClass, 'c');
        
        $qb2 = $this->entityManager->createQueryBuilder();
        $qb2->select('p.id')
            ->from($this->entityClass, 'chat')
            ->leftJoin('chat.participants', 'p')
            ->where("chat.id = c.id");
        
        $qb3 = $this->entityManager->createQueryBuilder();
        $qb3->select('p1.id')
            ->from($this->entityClass, 'chat1')
            ->leftJoin('chat1.participants', 'p1')
            ->where("chat1.id = c.id");
        
        $qb4 = $this->entityManager->createQueryBuilder();
        $qb4->select('p2.id')
            ->from(\App\Domain\Model\Chats\Message::class, 'message1')
            ->where("message1.id = message.id");
        
        $qb5 = $this->entityManager->createQueryBuilder();
        $qb5->select('message.id as lastMessageId')
            ->from(\App\Domain\Model\Chats\Message::class, 'message')
            ->where('message.deletedFor = 0')
            ->andWhere($qb->expr()->notIn(':user_id', $qb4->getDQL()))
            ->andWhere("chat2.id = c.id")
            ->setMaxResults(1)
            ->orderBy('message.id DESC')
            ->getDQL();
        
        if($interlocutorId) {            
            $qb->where($qb->expr()->in(':user_id', $qb2->getDQL()));
            $qb->andWhere($qb->expr()->in(':interlocutor_id', $qb3->getDQL()));
            $qb->setParameter('interlocutor_id', $interlocutorId);
        } else {
            $qb->where($qb->expr()->in(':user_id', $qb2->getDQL()));
        }
        
        if($cursor) {
            $qb->andWhere('c.id >= :cursor');
            $qb->setParameters(array('cursor' => $cursor));
        }
        
        if($type) {
            $qb->andWhere('c.type = :type');
            $qb->setParameter('type', $type);
        }
        
//        $qb->andWhere($qb->expr()->notIn(':user_id', $qb4->getDQL()));
        
        $qb->orderBy('lastMessageId', 'DESC');
        $qb->setParameter('user_id', $user->id());
            
        $res = $qb->setMaxResults($count)
             ->getQuery()
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')
            ->getResult();
//        echo count($res);exit();
        return $res;
    }

//    
//    function getPartOfActiveAndAccessibleToRequesterByOwnerOld(User $requester, User $owner, string $offsetId, int $count) {
//        $qb = $this->entityManager->createQueryBuilder();
//        
//        return $qb
//            ->select('p')
//            ->from($this->entityClass, 'p')
//            ->where('p.creator = :user')
//            ->andWhere('p.id > :offsetId')
//            ->setParameters(array('user' => $user, 'offsetId' => $offsetId))
//            ->setMaxResults($count)
//            ->getQuery()
//            //->useQueryCache(true)
//            //->setResultCacheId('kek')
//            //->useResultCache(true, 3600, 'kek')
//            ->getResult();
//    }
//    
//    function getFeed2(User $requester, ?string $cursor, int $count): array {
//        
//        $qb = $this->entityManager->createQueryBuilder();
//        $result = $qb
//            ->select('s.userId')
//            ->from(\App\Domain\Model\Users\Subscription\Subscription::class, 's')
//            ->where('s.subscriberId = :user_id')
//            ->setParameter('user_id', $requester->id())
//            ->getQuery()
//            //->useQueryCache(true)
//            //->setResultCacheId('kek')
//            //->useResultCache(true, 3600, 'kek')
//            ->getResult();
////        
//        $ids = [];
//        foreach ($result as $item) {
//            $ids[] = $item['userId'];
//        }
//        
//        $requesterId = $requester->id();
//        $ids = implode("','",$ids);
//        
//        $areConnected = "(
//            SELECT COUNT(id) FROM connections AS conn
//            WHERE ((conn.user1_id = '{$requesterId}' AND conn.user2_id = creator.id)
//            OR (conn.user1_id = creator.id AND conn.user2_id = '{$requesterId}'))
//            AND conn.is_accepted = 1
//        ) = 1";
//            
//        
//        $cursorPart = "";
//        if($cursor) {
//            $cursorPart = "AND post.id <= '{$cursor}'";
//        }
//        
//
//        $sql = "
//            SELECT
//                post.id AS id
//                FROM user_posts AS post
//                LEFT JOIN users AS creator ON post.creator_id = creator.id
//            WHERE creator.id IN ('{$ids}')
//            AND post.deleted = 0
//            $cursorPart
//            AND (
//                post.is_public = 1
//                OR
//                (post.is_public = 0 AND ($areConnected OR '{$requesterId}' = creator.id))
//            )
//            ORDER BY post.id DESC
//            LIMIT {$count}
//            ";
//            
//            //echo $sql;exit();
//        
//        $em = $this->entityManager;
//        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
//        $rsm->addScalarResult('id', 'id');
//        
//        $query = $em->createNativeQuery($sql, $rsm);
//        $res = $query->getArrayResult();
//        
//        $postIds = [];
//        foreach($res as $item) {
//            $postIds[] = $item['id'];
//        }
//        
//        return $em->getRepository(Post::class)->findBy(['id' => $postIds], ['id' => 'DESC']);
//    }
//    
//    function getFeed(User $requester, ?string $cursor, int $count) {
//        
//        $qb = $this->entityManager->createQueryBuilder();
//        $query1 = $qb
//            ->select('s.userId')
//            ->from(\App\Domain\Model\Users\Subscription\Subscription::class, 's')
//            ->where('s.subscriberId = :user_id')
//            ->setParameter('user_id', $requester->id())
//            ->getQuery()
//            //->useQueryCache(true)
//            //->setResultCacheId('kek')
//            //->useResultCache(true, 3600, 'kek')
//            ->getResult();
////        
//        $ids = [];
//        foreach ($result as $item) {
//            $ids[] = $item['userId'];
//        }
////        print_r($ids);exit();
//        
//        //$ids = ['01fnvhp4ppddp7kt1739f83p92', '01fnxev1mcx028scfkckhyqy0a'];
//        
//        $qb2 = $this->entityManager->createQueryBuilder();
//        $qb2
//            ->select('p')
//            ->from(Post::class, 'p')
//            ->innerJoin('p.creator', 'c')
//            ->where($qb2->expr()->in('c.id', $ids));
//            //->where($qb2->expr()->in('p.id', $ids)); 
//        
////        if($cursor) {
////            $qb->andWhere('u.id >= :cursor')
////               ->setParameter('cursor', $cursor);
////        }
//        //->setParameter('data', $ids) 
//        $qb2->setMaxResults($count);
//            //->setParameters(array('initiator_id' => $initiatorId, 'second_side_id' => $secondId))
//        $posts = $qb2->getQuery()->getResult();
//            //->useQueryCache(true)
//            //->setResultCacheId('kek')
//            //->useResultCache(true, 3600, 'kek')
//            
//        
//        foreach($posts as $post) {
//            echo $post->id() . '___';
//        }
//        echo 'end';
//        exit();
//        return $result;
//    }
//    
//    function getPartOfActiveAndAccessibleToRequesterByOwner(?User $requester, User $owner, ?string $cursor, int $count, string $order) {
//        //$areConnected = $requester->isConnectedWith($owner);
//        
//        $canSeeNonPublicPosts = false;
//        
//        if($requester) {
//            $qb1 = $this->entityManager->createQueryBuilder();
//            $qb1->select('f')
//                ->from(\App\Domain\Model\Users\Connection\Connection::class, 'f')
//                ->where('f.user1Id = :user1Id AND f.user2Id = :user2Id')
//                ->orWhere('f.user1Id = :user2Id AND f.user2Id = :user1Id')
//                ->setParameter('user1Id', $requester->id())
//                ->setParameter('user2Id', $owner->id());
//
//            $connection = $qb1->getQuery()->getOneOrNullResult();
//            if($connection && $connection->isAccepted()) {
//                $canSeeNonPublicPosts = true;
//            } elseif($requester->equals($owner)) {
//                $canSeeNonPublicPosts = true;
//            }
//        } else {
//            $canSeeNonPublicPosts = false;
//        }   
//
//        $qb = $this->entityManager->createQueryBuilder();
//        $qb->select('post')
//            ->from($this->entityClass, 'post')
//            ->where('post.creator = :owner')
//            ->andWhere('post.deleted = 0')
//            ->andWhere('post.deletedByGlobalModeration = 0');
//        
//        if(!$canSeeNonPublicPosts) {
//            $qb->andWhere('post.isPublic = 1');
//        }
//        
//        if($cursor) {
//            if($order === 'ASC') {
//                $qb->andWhere('post.id >= :cursor');
//            } elseif ($order === 'DESC') {
//                $qb->andWhere('post.id <= :cursor');
//            }
//            $qb->setParameters(array('owner' => $owner, 'cursor' => $cursor));
//        } else {
//            $qb->setParameters(array('owner' => $owner));
//        }
//        $query = $qb->setMaxResults($count)
//                ->orderBy('post.id', $order)
//            //->useQueryCache(true)
//            //->setResultCacheId('kek')
//            //->useResultCache(true, 3600, 'kek')
//            ->getQuery();
//        
//        return $query->getResult();
//    }
//    
//    function getCountOfActiveAndAccessibleToRequesterByOwner(?User $requester, User $owner) {
//        $canSeeNonPublicPosts = false;
//        
//        if($requester) {
//            $qb1 = $this->entityManager->createQueryBuilder();
//            $qb1->select('f')
//                ->from(\App\Domain\Model\Users\Connection\Connection::class, 'f')
//                ->where('f.user1Id = :user1Id AND f.user2Id = :user2Id')
//                ->orWhere('f.user1Id = :user2Id AND f.user2Id = :user1Id')
//                ->setParameter('user1Id', $requester->id())
//                ->setParameter('user2Id', $owner->id());
//
//            $connection = $qb1->getQuery()->getOneOrNullResult();
//            if($connection && $connection->isAccepted()) {
//                $canSeeNonPublicPosts = true;
//            } elseif($requester->equals($owner)) {
//                $canSeeNonPublicPosts = true;
//            }
//        } else {
//            $canSeeNonPublicPosts = false;
//        }   
//        
//        $repository = $this->entityManager->getRepository(Post::class);
//        $qb = $repository->createQueryBuilder('p')
//            ->select('count(p.id)')
//            ->where('p.creator = :owner')
//            ->andWhere('p.deleted = 0');
//        
//        if(!$canSeeNonPublicPosts) {
//            $qb->andWhere('p.isPublic = 1');
//        }
//        
//        $query = $qb->setParameter('owner', $owner)->getQuery();
//        
//        return (int)$query->getSingleScalarResult();
//    }
//
//    public function getById(string $id): ?Post {
//        return $this->entityManager->find($this->entityClass, $id);
//    }
//    
//    /** @return array<Post> */
//    public function getByOwnerId(string $id, ?string $ofsset, int $count): array {
//        
//    }
//    
//    /** @return array<int, Post> */
//    function getPartOfNotDeletedByOwner(User $owner, ?string $offsetId, int $count): array {
//        
//        $qb = $this->entityManager->createQueryBuilder();
//        $qb->select('post')
//            ->from($this->entityClass, 'post')
//            ->where('post.creator = :owner')
//            ->andWhere('post.deleted = 0')
//            ->andWhere('post.deletedByGlobalModeration = 0');
//        if($offsetId) {
//            $qb->andWhere('post.id > :offsetId');
//            $qb->setParameters(array('owner' => $owner, 'offsetId' => $offsetId));
//        } else {
//            $qb->setParameters(array('owner' => $owner));
//        }
//        $query = $qb->setMaxResults($count)
//            //->useQueryCache(true)
//            //->setResultCacheId('kek')
//            //->useResultCache(true, 3600, 'kek')
//            ->getQuery();
//        
//        return $query->getResult();
//    }
//    
//    function getCountOfActiveByOwner(User $owner): int {
//        $repository = $this->entityManager->getRepository(Post::class);
//        return (int)$repository->createQueryBuilder('p')
//            ->select('count(p.id)')
//            ->where('p.creator = :owner')
//            ->setParameter('owner', $owner)
//            ->getQuery()
//            ->getSingleScalarResult();
//    }


}
