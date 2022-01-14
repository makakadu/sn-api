<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\SavesCollection\Doctrine;

use App\Domain\Model\Users\SavesCollection\SavedItem;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Users\SavesCollection\SavedItemRepository;
use App\Domain\Model\Users\User\User;

class SavedItemDoctrineRepository extends AbstractDoctrineRepository implements SavedItemRepository {
    
    protected string $entityClass = SavedItem::class;
    /** @return array<int,SavedItem> */
    public function getPartByCollection(User $requester, string $collectionId, ?string $offsetId, int $count, string $order): array {
        $qb = $this->entityManager->createQueryBuilder();
        $query = $qb
            ->select('i')
            ->from($this->entityClass, 'i')
            ->where('i.collection = :collection_id');
        
        /*
        $typesCount = 0;
        foreach($types as $type) {
            if($type === 'photo') {
                if($typesCount) {
                    $query->orWhere("i.type = 'photo'");
                } else {
                    $query->andWhere("i.type = 'photo'");
                }
            } elseif($type === 'video') {
                if($typesCount) {
                    $query->orWhere("i.type = 'video'");
                } else {
                    $query->andWhere("i.type = 'video'");
                }
            } elseif($type === 'post') {
                if($typesCount) {
                    $query->orWhere("i.type = 'post'");
                } else {
                    $query->andWhere("i.type = 'post'");
                }
            }
            $typesCount += 1;
        }*/
        
        if($offsetId) {
            if($order === 'ASC') {
                $query->andWhere('i.id > :offset_id');
            } else {
                $query->andWhere('i.id < :offset_id');
            }
            $query->setParameter('offset_id', $offsetId);
        }
        return $query->setParameter('collection_id', $collectionId)
            ->setMaxResults($count)
            ->getQuery()
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')
            ->getResult();
    }

}
