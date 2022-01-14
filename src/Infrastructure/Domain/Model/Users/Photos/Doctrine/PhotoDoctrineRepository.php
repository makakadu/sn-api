<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\Photos\Doctrine;

use App\Domain\Model\Users\AlbumPhoto\AlbumPhoto;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Users\AlbumPhoto\AlbumPhotoRepository;
use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use Doctrine\ORM\EntityManager;

class PhotoDoctrineRepository extends AbstractDoctrineRepository implements AlbumPhotoRepository {
    protected string $entityClass = AlbumPhoto::class;
    
    private ConnectionRepository $connections;
    
    function __construct(EntityManager $entityManager, ConnectionRepository $connections) {
        $this->connections = $connections;
        $this->entityManager = $entityManager;
    }

    public function getById(string $id): ?AlbumPhoto {
        return $this->entityManager->find($this->entityClass, $id);
    }
    
//    function getPartOfAccessbileForRequesterByOwner(User $requester, User $owner) {
//        // Владелец мог закрыть профиль и если $requester не является его другом, то можно сразу понять и закончить выполнение. Такую проверку стоит делать в App сервисе
//        // Если профиль доступен для запрашивающего, то настройки приватности нужно проверять в запросе
//        // Возможно стоит сразу найти списки контактов владельца, где находится $requester
//        
//        $connection = $this->connections->getByUsers($requester, $owner);
//        $connectionsListsWithRequester = $connection ? $connection->connectionsLists() : []; // Чтобы это работало, нужно чтобы Connection содержало ссылки на все списки, в 
//        // которых оно находится, то есть нужно many-to-many bidirectional отношение
//        $criteria = Criteria::create()
//            ->where(Criteria::expr()->in($field, $values))
//            ->andWhere(Criteria::expr()->eq("banned", $requester));
//        $connectionsLists = count($owner->connectionsLists()->matching($criteria));
//    }
    
    // Получить все списки, где есть Connection. Получить все списки, для которых разрешен или запрещен доступ. Соединить их с помощью UNION ALL и затем сделать ещё раз
    // так же и соеденить с помощью UNION, затем сравнять 
    
    /** @return array<AlbumPhoto> */
    function getPartOfAccessbileForRequesterByOwner(
        ?User $requester, User $owner, bool $hideFromPosts, bool $hideFromComments,
        bool $hideTemp, bool $hidePictures, ?string $offsetId, ?int $count
    ): array {
        /*
        // https://stackoverflow.com/a/10597195/12293502
        // Здесь происходит извлечение всех ID друзей владельца и всех ID друзей запрашивающего. Они соединяются в одну коллекцию. После этого отсеиваются ID,
        // у которых нет дубликата в коллекции. После этого в коллекции остаются только те ID, у которых есть дубликаты. Затем дубликаты удаляются и происходит подсчёт
        // оставшихся ID.
         */
        $connection = $this->connections->getByUsersIds($requester->id(), $owner->id());
        $connectionId = $connection ? $connection->id() : '-1';

        $selectFromAlbums = $this->selectFromAlbums();
        $selectFromPosts = $hideFromPosts ? "" : $this->selectFromPosts();
        
        $selectFromCommetnsJoins = $hideFromComments ? "" : "
            LEFT JOIN user_photo_comments AS comment ON photo.comment_id = comment.id
            LEFT JOIN user_photos AS commented_photo ON comment.commented_id = commented_photo.id
            LEFT JOIN user_albums AS commented_photo_album ON commented_photo.album_id = commented_photo_album.id
            LEFT JOIN album_privacy_settings AS cpap ON commented_photo_album.who_can_see_id = cpap.id
        ";
        $selectFromPostJoin = $hideFromPosts ? "" : "
            LEFT JOIN user_posts AS post ON photo.post_id = post.id
        ";
        $selectFromComments = $hideFromComments ? "" : $this->selectFromComments();
        $selectPictures = $hidePictures ? "" : "OR ( picture.id IS NOT NULL )";
        $hideTempQuery = $hideTemp ? "AND photo.is_temp = 0" : "";
        echo $hidePictures;exit();
        $sql = "
        SELECT
        /* Если запрашивается коллекция фото, то нужны только ID и разные размеры*/
            photo.id, photo.original, photo.small, photo.medium, photo.extra_small, photo.large
            FROM user_photos AS photo
            LEFT JOIN users AS user ON photo.owner_id = user.id
            LEFT JOIN user_albums AS album ON photo.album_id = album.id
            LEFT JOIN album_privacy_settings AS privacy ON album.who_can_see_id = privacy.id
            $selectFromPostJoin
            $selectFromCommetnsJoins
            LEFT JOIN profile_pictures AS picture ON photo.id = picture.photo_id
        WHERE user.id = :owner_id

        $hideTempQuery
        AND (
            user.id = :requester_id
            $selectPictures
            $selectFromAlbums
            $selectFromPosts
            $selectFromComments
        )
        ";
        
        $em = $this->entityManager;
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('original', 'original');
        $rsm->addScalarResult('small', 'small');
        $rsm->addScalarResult('medium', 'medium');
        $rsm->addScalarResult('extra_small', 'extra_small');
        $rsm->addScalarResult('large', 'large');
        //$rsm->addScalarResult('PID', 'PID');
        
        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter(':requester_id', $requester->id());
        $query->setParameter(':owner_id', $owner->id());
        $query->setParameter(':connection_id', $connectionId);

        $res = $query->getArrayResult();
        print_r($res);exit();
        foreach($res as $id) {
            print_r($id);
        }
    }
    
    function findBan() {
        return "(SELECT COUNT(*) FROM user_bans ban WHERE ban.owner_id = :owner_id AND ban.banned_id = :requester_id)";
    }
    
    function isNotBanned() {
        return "((SELECT count(*) FROM user_bans ban WHERE ban.owner_id = :owner_id AND ban.banned_id = :requester_id) = 0)";
    }
    
    function findConnection() {
        return "FROM connections AS fr WHERE (fr.user1_id = user.id AND fr.user2_id = :requester_id) OR (fr.user1_id = :requester_id AND fr.user2_id = user.id)";
    }
    
    function selectFromPosts() {
        return "
            OR (
                post.id IS NOT NULL
                AND (
                    post.is_public = 1
                    OR
                    post.creator_id = :requester_id
                    OR (
                        post.is_public = 0 AND :connection_id != '-1'
                    )
                )
            )
        ";
    }

    function selectFromAlbums() {
        return "
            OR (
                album.id IS NOT NULL
                AND (
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
                        AND ( /* Либо друг, который не находится в запрещенных списках */
                            (
                                :connection_id != '-1'
                                AND
                                :connection_id NOT IN ({$this->unallowedConns()})
                                AND
                                {$this->inUnallowedListsMatchesCount()} = 0
                            )
                            /* Либо НЕ друг, с которым есть общие connections и который не забанен */
                            OR (
                                :connection_id = '-1' /* Должно быть равно -1*/                                
                                AND
                                {$this->haveCommonFriends()}
                            )
                        )
                    )
                    OR (privacy.access_level = 4)
                )
            )
        ";
    }
    
    function selectFromComments() {
        return "
            OR (
                commented_photo_album.id IS NOT NULL
                AND (
                    (
                        cpap.access_level = 1
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
                        cpap.access_level = 2
                        AND (
                            :connection_id != '-1'
                            AND
                            (:connection_id) NOT IN ({$this->unallowedConns()})
                            AND
                            {$this->inUnallowedListsMatchesCount()} = 0
                        )
                    )
                    OR (
                        cpap.access_level = 3
                        AND ( /* Либо друг, который не находится в запрещенных списках */
                            (
                                :connection_id != '-1'
                                AND
                                :connection_id NOT IN ({$this->unallowedConns()})
                                AND
                                {$this->inUnallowedListsMatchesCount()} = 0
                            )
                            /* Либо НЕ друг, с которым есть общие connections и который не забанен */
                            OR (
                                :connection_id = '-1' /* Должно быть равно -1*/                                
                                AND
                                {$this->haveCommonFriends()}
                            )
                        )
                    )
                    OR (cpap.access_level = 4)
                )
            )
        ";
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
