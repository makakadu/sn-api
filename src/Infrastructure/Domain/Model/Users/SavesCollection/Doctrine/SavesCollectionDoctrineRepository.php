<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\SavesCollection\Doctrine;

use App\Domain\Model\Users\SavesCollection\SavesCollection;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Users\SavesCollection\SavesCollectionRepository;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use Doctrine\ORM\EntityManager;

class SavesCollectionDoctrineRepository extends AbstractDoctrineRepository implements SavesCollectionRepository {
    protected string $entityClass = SavesCollection::class;
    
    private ConnectionRepository $connections;
    
    function __construct(EntityManager $entityManager, ConnectionRepository $connections) {
        $this->connections = $connections;
        $this->entityManager = $entityManager;
    }
    
    /**
     * @return array<int,SavesCollection>
     */
    function getPartOfActiveAndAccessibleToRequesterByOwner(User $requester, User $creator, int $count, string $order, ?string $offsetId): array {
        /*
         * Здесь похожая ситуация как с альбомами, какие-то доступны для $requester, а какие-то нет. Это нужно проверить в SQL запросе, иначе не получится.
         */
        
        $connection = $this->connections->getByUsersIds($requester->id(), $creator->id());
        $connectionId = $connection ? $connection->id() : '-1';
        
        $offset = "";
        if($offsetId) {
            $offset = $order === 'ASC' ? "AND collection.id > :offset_id" : "AND collection.id < :offset_id";
        }
        $limit = $count ? $count : 20;

        $sql = "
        SELECT
            collection.id as id
            FROM saves_collections AS collection
            LEFT JOIN users AS user ON collection.creator_id = user.id
            LEFT JOIN saves_collection_privacy AS privacy ON collection.id = privacy.collection_id
        WHERE user.id = :creator_id
        AND (
            ( 
                privacy.access_level = 0
                AND
                collection.creator_id = :requester_id
            )
            OR
            (
                privacy.access_level = 1
                AND (
                    :connection_id != '-1'
                    AND
                    (
                        :connection_id IN ({$this->allowedConns()})
                        OR
                        {$this->inAllowedListsMatchesCount()} > 0
                    )
                    AND :connection_id NOT IN ({$this->unallowedConns()})
                )
            )
            OR (
                privacy.access_level = 2
                AND (
                    :connection_id != '-1'
                    AND
                    (:connection_id) NOT IN ({$this->unallowedConns()})
                    AND
                    {$this->inUnallowedListsMatchesCount()} = 0
                )
            )
            OR (
                privacy.access_level = 3
                AND (
                    (
                        :connection_id != '-1'
                        AND
                        :connection_id NOT IN ({$this->unallowedConns()})
                        AND
                        {$this->inUnallowedListsMatchesCount()} = 0
                    )
                    OR (
                        :connection_id = '-1'                        
                        AND
                        {$this->haveCommonFriends()}
                    )
                )
            )
            OR (privacy.access_level = 4)
        )
        $offset
        ORDER BY id $order
        LIMIT $limit
        ";
                        
        //echo $sql;exit();
                        
        $em = $this->entityManager;
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        
        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter(':requester_id', $requester->id());
        $query->setParameter(':creator_id', $creator->id());
        $query->setParameter(':connection_id', $connectionId);
        $query->setParameter(':offset_id', $offsetId);

        $res = $query->getArrayResult();
        $ids = [];
        foreach ($res as $item) {
            $ids[] = $item['id'];
        }
        
        $repository = $this->entityManager->getRepository($this->entityClass);
        return $repository->findBy(array('id' => $ids));
        
//        foreach ($collections as $coll) {
//            echo $coll->id() . '___________';
//        }
//        exit();
    }

    function findConnection() {
        return "FROM connections AS fr WHERE (fr.user1_id = user.id AND fr.user2_id = :requester_id) OR (fr.user1_id = :requester_id AND fr.user2_id = user.id)";
    }

    function getById(string $id): ?SavesCollection {
        return $this->entityManager->find($this->entityClass, $id);
    }
    
    function allowedConns() {
        return "SELECT conn.id from connections AS conn 
                LEFT JOIN album_privacy_ac AS apac ON conn.id = apac.connection_id 
                WHERE apac.setting_id = privacy.id";
    }
    
    function unallowedConns() {
        return "SELECT conn.id from connections AS conn 
                LEFT JOIN album_privacy_uc AS apuc ON conn.id = apuc.connection_id 
                WHERE apuc.setting_id = privacy.id";
    }
    
    function haveCommonFriends() {
        return "
            (SELECT COUNT(*) FROM (
                SELECT * FROM (
                    (SELECT user1_id as user_Id FROM connections WHERE user2_id = user.id OR user2_id = :requester_id)
                    UNION ALL
                    (SELECT user2_id as user_Id FROM connections WHERE user1_id = user.id OR user1_id = :requester_id)
                ) As tbl GROUP BY tbl.user_id HAVING COUNT(*)=2
            ) as qwe)
        "; 
    }
    
    function inAllowedListsMatchesCount() {
        return "
            (SELECT COUNT(*) FROM(
                SELECT * FROM (
                    (
                    SELECT cl.id FROM connections_lists AS cl
                    LEFT JOIN list_connections AS lc ON cl.id = lc.list_id
                    WHERE lc.connection_id = (SELECT fr.id {$this->findConnection()})
                    )
                    UNION ALL
                    (
                    SELECT cl.id FROM connections_lists AS cl
                    LEFT JOIN album_privacy_al AS al ON cl.id = al.list_id
                    WHERE al.setting_id = privacy.id
                    )
                ) AS tbl GROUP BY tbl.id HAVING COUNT(*)=2
            ) AS matches_count)
        ";
    }
        
    function inUnallowedListsMatchesCount() {
        return "
            (SELECT COUNT(*) FROM(
                SELECT * FROM (
                    (
                    SELECT cl.id FROM connections_lists AS cl
                    LEFT JOIN list_connections AS lc ON cl.id = lc.list_id
                    WHERE lc.connection_id = (SELECT fr.id {$this->findConnection()})
                    )
                    UNION ALL
                    (
                    SELECT cl.id FROM connections_lists AS cl
                    LEFT JOIN album_privacy_ul AS ul ON cl.id = ul.list_id
                    WHERE ul.setting_id = privacy.id
                    )
                ) AS tbl GROUP BY tbl.id HAVING COUNT(*)=2
            ) AS matches_count )
        ";
    }
}
