<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Chats\Doctrine;

use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Chats\Message;
use App\Domain\Model\Chats\MessageRepository;
use App\Domain\Model\Chats\Chat;

class MessageDoctrineRepository extends AbstractDoctrineRepository implements MessageRepository {
    protected string $entityClass = Message::class;
    
    public function getById(string $id): ?Message {
        return $this->entityManager->find($this->entityClass, $id);
    }
    
    public function getChatMessages(User $requester, Chat $chat, ?string $cursor, int $count, ?string $order = 'DESC'): array {
        
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('m');
        $qb->from($this->entityClass, 'm');
        $qb->where('m.chat = :chat');
        
        $qb4 = $this->entityManager->createQueryBuilder();
        $qb4->select('message.deletedFor')
            ->from($this->entityClass, 'message')
            ->where("m.id = message.id");
        
        if($cursor) {
            $qb->andWhere('m.id <= :cursor');
            $qb->setParameters(array('cursor' => $cursor));
        }
        
        $qb->andWhere($qb->expr()->notIn(':user_id', $qb4->getDQL()));
        
        $qb->orderBy('m.id', $order);
        $qb->setParameter('user_id', $requester->id());
        $qb->setParameter('chat', $chat);
            
        $res = $qb->setMaxResults($count)
             ->getQuery()
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')
            ->getResult();
        return $res;
    }

}
