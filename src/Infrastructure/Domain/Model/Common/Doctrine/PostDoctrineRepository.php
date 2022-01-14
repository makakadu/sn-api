<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Common\Doctrine;

use App\Domain\Model\Common\PostRepository;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Common\PostInterface;
use App\Domain\Model\Users\User\User;

class PostDoctrineRepository extends AbstractDoctrineRepository implements PostRepository {
    protected string $entityClass = PostInterface::class;
    
    function getAllAccessibleToRequester(
        ?User $requester,
        ?string $offset,
        ?string $text,
        int $count,
        string $order,
        int $commentsCount,
        string $commentsOrder,
        string $commentsType,
        int $hideFromUsers,
        int $hideFromGroups,
        int $hideFromPages
    ): array {
        //echo $commentsCount;exit();
       // echo $hideFromPages;exit();
        if($hideFromUsers && $hideFromGroups && $hideFromPages) {
            return [];
        }
        
        $offsetClause = "";
        if($offset) {
            if($order === 'ASC') {
                $offsetClause = "AND post.id > :offset";
            } elseif($order === "DESC") {
                $offsetClause = "AND post.id < :offset ";
            }
        }
        
        $selectOnlyRootUserPostComments = $commentsType === "root" ? "AND user_post_comment.root_id IS NULL" : "";
        $selectOnlyRootGroupPostComments = $commentsType === "root" ? "AND group_post_comment.root_id IS NULL" : "";
        
        $searchByTextClause = $text ? "AND text LIKE '%$text%'" : "";
        
        $areConnected = "
            (
                SELECT COUNT(id) FROM connections AS conn
                WHERE (conn.user1_id = owner.id AND conn.user2_id = :requester_id)
                OR (conn.user1_id = :requester_id AND conn.user2_id = owner.id)
            ) = 1
        ";
        
        $requesterIsMember = "
            (
                SELECT COUNT(id) FROM memberships AS membership
                WHERE (membership.member_id = :requester_id AND membership.group_id = _group.id)
            ) = 1
        ";

        $photoJSON = "'id', id, 'original', original, 'small', small, 'extra_small', extra_small";
        
        $selectDifferentUserPostReactionTypesCount = "";
        foreach(\App\Domain\Model\Users\Post\Reaction::reactionsTypes() as $reactionType) {
            $selectDifferentUserPostReactionTypesCount .= 
            "(SELECT COUNT(*) FROM user_post_reactions WHERE user_post_reactions.post_id = post.id AND reaction_type = '$reactionType') AS {$reactionType}_reactions_count,
            ";
        }
        $selectDifferentGroupPostReactionTypesCount = "";
        foreach(\App\Domain\Model\Users\Post\Reaction::reactionsTypes() as $reactionType) {
            $selectDifferentGroupPostReactionTypesCount .= 
            "(SELECT COUNT(*) FROM group_post_reactions WHERE group_post_reactions.post_id = post.id AND reaction_type = '$reactionType') AS {$reactionType}_reactions_count,
            ";
        }
        
        $selectDifferentUserPostCommentReactionTypesCount = "";
        foreach(\App\Domain\Model\Users\Post\Reaction::reactionsTypes() as $reactionType) {
            $selectDifferentUserPostCommentReactionTypesCount .= "
                '{$reactionType}',
                (SELECT COUNT(*) FROM user_post_comment_reactions AS comment_reaction
                WHERE comment_reaction.comment_id = user_post_comment.id
                AND comment_reaction.reaction_type = '$reactionType'),";
        }
        $selectDifferentUserPostCommentReactionTypesCount = \substr(
            $selectDifferentUserPostCommentReactionTypesCount,
            0,
            \strlen($selectDifferentUserPostCommentReactionTypesCount) - 1
        );
        
        $selectDifferentGroupPostCommentReactionTypesCount = "";
        foreach(\App\Domain\Model\Users\Post\Reaction::reactionsTypes() as $reactionType) {
            $selectDifferentGroupPostCommentReactionTypesCount .= "
                '{$reactionType}',
                (SELECT COUNT(*) FROM group_post_comment_reactions AS comment_reaction
                WHERE comment_reaction.comment_id = group_post_comment.id
                AND comment_reaction.reaction_type = '$reactionType'),";
        }
        $selectDifferentGroupPostCommentReactionTypesCount = \substr(
            $selectDifferentGroupPostCommentReactionTypesCount,
            0,
            \strlen($selectDifferentGroupPostCommentReactionTypesCount) - 1
        );
        //echo $hideFromUsers;exit();
        
        $selectShared = "
            (SELECT JSON_OBJECT('id', user_photo.id, 'medium', user_photo.medium) FROM shared_user_photos AS shared
            LEFT JOIN user_photos AS user_photo ON shared.photo_id = user_photo.id WHERE shared.id = post.shared_id) AS shared_user_photo,
            (SELECT JSON_OBJECT('id', group_photo.id, 'medium', group_photo.medium) FROM shared_group_photos AS shared
            LEFT JOIN group_photos AS group_photo ON shared.photo_id = group_photo.id WHERE shared.id = post.shared_id) AS shared_group_photo
        ";

        $getUserPostsSql = $hideFromUsers ? "" : "(
        SELECT
            post.id AS id,
            post.text AS text,
            'user_post' AS type,
            
            owner.id AS owner_id,
            CONCAT(owner.first_name, ' ', owner.last_name) AS owner_name,
            (SELECT picture.small FROM profile_pictures AS picture WHERE picture.user_id = owner.id ORDER BY updated_at DESC LIMIT 1) AS owner_picture,
            
            NULL as group_id,
            NULL as group_name,
            NULL AS group_picture,
            
            NULL as creator_id,
            NULL as creator_name,
            NULL as creator_picture,
            
            
            (SELECT COUNT(*) FROM user_post_comments WHERE user_post_comments.commented_id = post.id) AS comments_count,
            (SELECT COUNT(*) FROM user_post_reactions WHERE user_post_reactions.post_id = post.id) AS reactions_count,
            $selectShared,
            $selectDifferentUserPostReactionTypesCount
            (SELECT JSON_ARRAYAGG(json_object($photoJSON)) FROM user_photos AS photo WHERE photo.post_id = post.id) AS photos,
            (
                SELECT JSON_ARRAYAGG(comment_data) FROM (
                    SELECT
                    JSON_OBJECT(
                        'id', id,
                        'text', text,
                        'replies_count',
                        (SELECT COUNT(*) FROM user_post_comments AS comment_reply WHERE comment_reply.root_id = user_post_comment.id),
                        'reactions_count',
                        json_object(
                            'all',
                            (SELECT COUNT(*) FROM user_post_comment_reactions AS comment_reaction WHERE comment_reaction.comment_id = user_post_comment.id),
                            $selectDifferentUserPostCommentReactionTypesCount
                        )
                    ) AS comment_data
                    FROM user_post_comments AS user_post_comment
                    WHERE user_post_comment.commented_id = post.id
                    $selectOnlyRootUserPostComments
                    ORDER BY
                        (SELECT COUNT(*) FROM user_post_comments AS comment_reply WHERE comment_reply.root_id = user_post_comment.id) DESC,
                        (SELECT COUNT(*) FROM user_post_comment_reactions AS comment_reaction WHERE comment_reaction.comment_id = user_post_comment.id) DESC
                    LIMIT $commentsCount
                ) AS comments
            ) AS comments
            FROM user_posts AS post
            LEFT JOIN users AS owner ON post.creator_id = owner.id
        WHERE (
            post.is_public = 1
            OR
            (post.is_public = 0 AND ($areConnected OR :requester_id = owner.id))
        )
        $offsetClause
        $searchByTextClause
        )";
        
        $union = $hideFromUsers ? "" : " UNION ";
        $getGroupPostsSql = $hideFromGroups ? "" : "$union(
        SELECT
            post.id AS id,
            post.text AS text,
            'group_post' AS type,
            
            NULL AS owner_id,
            NULL AS owner_name,
            NULL AS owner_picture,
            
            _group.id AS group_id,
            _group.name AS group_name,
            (SELECT picture.small FROM group_pictures AS picture WHERE picture.group_id = _group.id ORDER BY updated_at DESC LIMIT 1) AS group_picture,
            
            creator.id AS creator_id,
            CONCAT(creator.first_name, ' ', creator.last_name) AS creator_name,
            (SELECT picture.small FROM profile_pictures AS picture WHERE picture.user_id = creator.id ORDER BY updated_at DESC LIMIT 1) AS creator_picture,
            
            (SELECT COUNT(*) FROM group_post_comments WHERE group_post_comments.commented_id = post.id) AS comments_count,
            (SELECT COUNT(*) FROM group_post_reactions WHERE group_post_reactions.post_id = post.id) AS reactions_count,
            $selectShared,
            $selectDifferentGroupPostReactionTypesCount
            (SELECT JSON_ARRAYAGG(json_object($photoJSON)) FROM group_photos AS photo WHERE photo.post_id = post.id) AS photos,
            (
                SELECT JSON_ARRAYAGG(comment_data) FROM (
                    SELECT JSON_OBJECT(
                        'id', id,
                        'text', text,
                        'replies_count',
                        (SELECT COUNT(*) FROM group_post_comments AS comment_reply WHERE comment_reply.root_id = group_post_comment.id),
                        'reactions_count',
                        json_object(
                            'all',
                            (SELECT COUNT(*) FROM group_post_comment_reactions AS comment_reaction WHERE comment_reaction.comment_id = group_post_comment.id),
                            $selectDifferentGroupPostCommentReactionTypesCount
                        )
                    ) AS comment_data
                
                    FROM group_post_comments AS group_post_comment
                    WHERE group_post_comment.commented_id = post.id
                    $selectOnlyRootGroupPostComments
                    ORDER BY
                        (SELECT COUNT(*) FROM group_post_comments AS comment_reply WHERE comment_reply.root_id = group_post_comment.id) DESC,
                        (SELECT COUNT(*) FROM group_post_comment_reactions AS comment_reaction WHERE comment_reaction.comment_id = group_post_comment.id) DESC
                    LIMIT $commentsCount
                ) AS comments
            ) AS comments
            FROM group_posts AS post
            LEFT JOIN _groups AS _group ON post.group_id = _group.id
            LEFT JOIN users AS creator ON post.creator_id = creator.id
        WHERE (
            _group.is_private = 0 /* Если группа не приватная, то её контент виден всем */
            OR
            (_group.is_private = 1 AND $requesterIsMember) /* Если приватная, то только участникам */
        )
        $offsetClause
        $searchByTextClause
        )";
        
        $sql = "
            $getUserPostsSql
            $getGroupPostsSql
            ORDER BY id $order /* ORDER BY должен быть именно здесь */
            LIMIT :count
        ";

        $em = $this->entityManager;
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        
        $rsm->addScalarResult('owner_id', 'owner_id');
        $rsm->addScalarResult('owner_name', 'owner_name');
        $rsm->addScalarResult('owner_picture', 'owner_picture');
        
        $rsm->addScalarResult('group_id', 'group_id');
        $rsm->addScalarResult('group_name', 'group_name');
        $rsm->addScalarResult('group_picture', 'group_picture');
        
        $rsm->addScalarResult('creator_id', 'creator_id');
        $rsm->addScalarResult('creator_name', 'creator_name');
        $rsm->addScalarResult('creator_picture', 'creator_picture');
        
        $rsm->addScalarResult('type', 'type');
        $rsm->addScalarResult('text', 'text');
        $rsm->addScalarResult('photos', 'photos');
        
        $rsm->addScalarResult('comments', 'comments');
        $rsm->addScalarResult('comments_count', 'comments_count');
        $rsm->addScalarResult('reactions_count', 'reactions_count');
        
        $rsm->addScalarResult('shared_user_photo', 'shared_user_photo');
        $rsm->addScalarResult('shared_group_photo', 'shared_group_photo');
        
        foreach(\App\Domain\Model\Users\Post\Reaction::reactionsTypes() as $reactionType) {
            $rsm->addScalarResult("{$reactionType}_reactions_count", "{$reactionType}_reactions_count");
        }
        
        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter(':requester_id', $requester->id());
        if($offset) {
            $query->setParameter(':offset', $offset);
        }
        $query->setParameter(':count', $count);

        $res = $query->getArrayResult();
        $prepared = [];
        //print_r($res);exit();
        foreach($res as $key => $value) {
            //print_r($value['photos']);exit();
            $photos = $value['photos'];
            $comments = $value['comments'];
            
            $reactionsCount = [];
            $reactionsCount['all'] = $value['reactions_count'];
            foreach(\App\Domain\Model\Users\Post\Reaction::reactionsTypes() as $reactionType) {
                $reactionsCount[$reactionType] = $value["{$reactionType}_reactions_count"];
            }
            
            $post = [];

            $post['id'] = $value['id'];
            $post['type'] = $value['type'];
            $post['text'] = $value['text'];
            $post['comments_count'] = $value['comments_count'];
            $post['photos'] = $photos ? \json_decode($photos, true) : [];
            $post['comments'] = $comments ? \json_decode($comments, true) : [];
            
            $post['reactions_count'] = $reactionsCount;
            
            $sharedUserPhoto = $value['shared_user_photo'];
            if($sharedUserPhoto) {
                $post['shared_user_photo'] = \json_decode($sharedUserPhoto, true);
            }
            $sharedGroupPhoto = $value['shared_group_photo'];
            if($sharedGroupPhoto) {
                $post['shared_group_photo'] = \json_decode($sharedGroupPhoto, true);
            }
            if($value['type'] === 'user_post') {
                $post['owner'] = [
                    'owner_id' => $value['owner_id'],
                    'owner_name' => $value['owner_name'],
                    'owner_picture' => $value['owner_picture']
                ];
                $post['creator'] = [
                    'owner_id' => $value['owner_id'],
                    'owner_name' => $value['owner_name'],
                    'owner_picture' => $value['owner_picture']
                ];
            } else {
                $post['group'] = [
                    'group_id' => $value['group_id'],
                    'group_name' => $value['group_name'],
                    'group_picture' => $value['group_picture']
                ];
                $post['creator'] = [
                    'creator_id' => $value['creator_id'],
                    'creator_name' => $value['creator_name'],
                    'creator_picture' => $value['creator_picture']
                ];
            }
            
            $prepared[$key] = $post;
        }
        print_r($prepared);exit();
    }
    

    
}

