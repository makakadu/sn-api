<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Common\Reactions\Doctrine;

use App\Domain\Model\Common\Reaction;
use App\Domain\Model\Common\ReactionRepository;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Users\User\User;

class ReactionDoctrineRepository extends AbstractDoctrineRepository implements ReactionRepository {
    
    protected string $entityClass = 'kek';

    
    function addSlash(string $string): string {
        return \str_replace("\\", "\\\\", $string);
    }
    
    /**
     * @param array<int,string> $types
     * @return array<int,Reactable>
     */
    function getPartCreatedOnBehalfOfUser(User $creator, int $count, ?string $offsetId, array $types): array{
        $offset = $offsetId ? "AND id > '$offsetId'" : "";
        
        /*
         * Нужно извлечь только те реакции, которые созданы от имени пользователя. Лушче отделить их здесь в репозитории, потому что так возвратится нужное
         * количество реакций, а если отделить их в апп сервисе, то придётся запрашивать ещё, если хотя бы одна реакция создана НЕ от имени пользователя
         */

        
        $selectPostsReactions = 
            "(SELECT id, '".$this->addSlash(\App\Domain\Model\Users\Post\Reaction::class)."' as type
            FROM user_post_reactions AS upr
            WHERE upr.creator_id = :creator_id AND asPage_id IS NULL
            $offset
            LIMIT :count)
            UNION
            (SELECT id, '".$this->addSlash(\App\Domain\Model\Groups\Post\Reaction::class)."' as type
            FROM group_post_reactions AS gpr
            WHERE gpr.creator_id = :creator_id AND as_group = 0
            $offset
            LIMIT :count)
            UNION
            (SELECT id, '".$this->addSlash(\App\Domain\Model\Pages\Post\Reaction::class)."' as type
            FROM page_post_reactions AS ppr
            WHERE ppr.creator_id = :creator_id AND asPage_id IS NULL
            $offset
            LIMIT :count)
            ";
        
        $selectPhotosReactions =
            "(SELECT id, '".$this->addSlash(\App\Domain\Model\Users\Photos\Reaction::class)."' as type
            FROM user_photo_reactions AS uphr
            WHERE uphr.creator_id = :creator_id AND asPage_id IS NULL
            $offset
            LIMIT :count)
            UNION
            (SELECT id, '".$this->addSlash(\App\Domain\Model\Groups\Photos\Reaction::class)."' as type
            FROM group_photo_reactions AS gphr
            WHERE gphr.creator_id = :creator_id AND as_group = 0
            $offset
            LIMIT :count)
            UNION
            (SELECT id, '".$this->addSlash(\App\Domain\Model\Pages\Photos\Reaction::class)."' as type
            FROM page_photo_reactions AS pphr
            WHERE pphr.creator_id = :creator_id AND asPage_id IS NULL
            $offset
            LIMIT :count)
            ";
        
        $selectVideosReactions =
            "(SELECT id, '".$this->addSlash(\App\Domain\Model\Users\Videos\Reaction::class)."' as type
            FROM user_video_reactions AS uvr
            WHERE uvr.creator_id = :creator_id AND asPage_id IS NULL
            $offset
            LIMIT :count)
            UNION
            (SELECT id, '".$this->addSlash(\App\Domain\Model\Users\Videos\Reaction::class)."' as type
            FROM group_video_reactions AS gvr
            WHERE gvr.creator_id = :creator_id AND as_group = 0
            $offset
            LIMIT :count)
            UNION
            (SELECT id, '".$this->addSlash(\App\Domain\Model\Pages\Videos\Reaction::class)."' as type
            FROM page_video_reactions AS pvr
            WHERE pvr.creator_id = :creator_id AND asPage_id IS NULL
            $offset
            LIMIT :count)
            ";
            
        $selectCommentsReactions = 
            "(SELECT id, '".$this->addSlash(\App\Domain\Model\Users\Comments\Reaction::class)."' as type
            FROM profile_comment_reactions AS pcr
            WHERE pcr.creator_id = :creator_id AND asPage_id IS NULL
            $offset
            LIMIT :count)
            UNION
            (SELECT id, '".$this->addSlash(\App\Domain\Model\Users\Comments\Reaction::class)."' as type
            FROM group_comment_reactions AS gcr
            WHERE gcr.creator_id = :creator_id AND as_group = 0
            $offset
            LIMIT :count)
            UNION
            (SELECT id, '".$this->addSlash(\App\Domain\Model\Pages\Comments\Reaction::class)."' as type
            FROM page_comment_reactions AS pcr
            WHERE pcr.creator_id = :creator_id AND asPage_id IS NULL
            $offset
            LIMIT :count)
            ";

        $sql = "";
        $typesCount = 0;
        if(\in_array("photos", $types)) {
            $sql .= $selectPhotosReactions;
            $typesCount++;
        }
        if(\in_array("videos", $types)) {
            if($typesCount) $sql .= " UNION ";
            $sql .= $selectVideosReactions;
            $typesCount++;
        }
        if(\in_array("posts", $types)) {
            if($typesCount) $sql .= " UNION ";
            $sql .= $selectPostsReactions;
            $typesCount++;
        }
        if(\in_array("photos", $types)) {
            if($typesCount) $sql .= " UNION ";
            $sql .= $selectCommentsReactions;
        }
        $sql .= " LIMIT :count";
        
        $em = $this->entityManager;
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('type', 'type');
        
        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter(':count', $count);
        $query->setParameter(':creator_id', $creator->id());
        
        $result = $query->getArrayResult();
        
        /*
         * В этот массив будут помещены типы реакций, которые нужно извлечь (чем меньше разных типов, тем меньше нужно использовать репозиториев и запросов в БД)
         * Этот массив будет содержать подмассивы с инфой о реакциях, которые будут сгрупированы по типу реакции как-то так:
         * [
         *     "photo_reaction" => [
         *         "id" => 123,
         *         "id" => 789,
         *         "id" => 456,
         *     ],
         *     'post_reaction' => [
         *         "id" => 234,
         *         "id" => 345
         *     ],
         *     'video_reaction' => [
         *         "id" => 678,
         *         "id" => 567
         *     ]
         * ]
         */
        $types = []; 
        $grouped = [];
        
        foreach($result as $item) {
            //print_r($item);exit();
            $types[] = $item['type'];
            $grouped[$item['type']][] = ['id' => $item['id']]; 
        }
        $reactions = []; // Сюда будут добавлять полученные реальные реакции

        /*
         * Получаем уже реальные реакции
         * Также здесь из массива $types удаляются повторяющиеся типы
         */
        foreach(\array_values(\array_unique($types)) as $type) {
            $repository = $this->entityManager->getRepository($type);
            $reactions = \array_merge($reactions, $repository->findBy(array('id' => $this->getIds($grouped[$type]))));
        }
        
        \usort($reactions, fn(Reaction $a, Reaction $b) => $a->id() > $b->id());
        
        return $reactions;
    }
    
    // Этот массив нужен для получения массива ID реакций, чтобы с помощью этих ID получить нужные реакции.
    // Он нужен, вроде бы, только в методе getPartCreatedOnBehalfOfUser()
    function getIds(array $entities): array {
        $ids = [];
        foreach ($entities as $entity) {
            $ids[] = $entity['id'];
        }
        return $ids;
    }

}
