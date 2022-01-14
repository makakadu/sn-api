<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\Post\Doctrine;

use App\Domain\Model\Users\Post\ReactionRepository;
use App\Domain\Model\Users\Post\Reaction;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Users\Post\Post;
use App\Domain\Model\Users\User\User;

class ReactionDoctrineRepository extends AbstractDoctrineRepository implements ReactionRepository {
    protected string $entityClass = Reaction::class;
    
    function getPart(string $postId, ?string $offsetId, int $limit): array {
        /** @var \Doctrine\ORM\QueryBuilder $qb */
        $qb = $this->entityManager->createQueryBuilder();
        $qb ->select('comment')
            ->from($this->entityClass, 'comment')
            ->where(
                    '(comment.post = :postId AND comment.replies IS NOT EMPTY)'
                  . 'OR (comment.post = :postId AND comment.deletedAt IS NULL)')          // Комменты, к которым есть хотя бы один ответ. Или где deletedAt равен null
            ->setParameter(':postId', $postId)
            ->setMaxResults($limit);
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')

        if($offsetId) {
            $qb->andWhere('comment.id > :last')
                ->setParameter(':last', $offsetId);
        }
        return $qb->getQuery()->getResult();
    }

    public function getById(string $id): ?Comment {
        return $this->entityManager->find($this->entityClass, $id);
    }
    
    function getByPostAndUser(User $requester, Post $post): ?Reaction {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('r')
            ->from(Reaction::class, 'r')
            ->where('r.post = :post')
            ->andWhere('r.creator = :requester')
            ->setParameter('requester', $requester)
            ->setParameter('post', $post);
        
        return $qb->getQuery()->getOneOrNullResult();
    }
    
    function getPartOfActiveByPost(Post $post, ?string $offsetId, int $count, string $type, string $order): array {
        $qb = $this->entityManager->getRepository(Comment::class)
            ->createQueryBuilder('comment');
        
        $commentsOrder = \strtoupper($order);
        //echo $commentsOrder;exit();

        $qb->where('comment.commented = :post')
            ->andWhere('comment.isDeleted = 0');
        if($type === "root") {
            $qb->andWhere('comment.root IS NULL');
        }
        $qb->setParameter('post', $post);
        
        $qb->setMaxResults($count);
        /*        if($commentsOrder === 'TOP') {
            $qb->add('orderBy', "mainSort ASC, reactions_and_replies_count DESC, CASE WHEN mainSort = 0 THEN id END DESC, CASE WHEN mainSort = 1 THEN id END ASC");
            $qb->setParameter('requester', $requester);
        } else*/
        if($commentsOrder === 'DESC') {
            if($offsetId) {
                $qb->andWhere('comment.id < :offsetId');
                $qb->setParameter('offsetId', $offsetId);
            }
            $qb->orderBy('comment.id', 'DESC');
        } elseif($commentsOrder === 'ASC') {
            if($offsetId) {
                $qb->andWhere('comment.id > :offsetId');
                $qb->setParameter('offsetId', $offsetId);
            }
        }
        
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')
        $query = $qb->getQuery();
        return $query->getResult();
    }
    
    function getCountByPost(Post $post): int {
        $repository = $this->entityManager->getRepository(Comment::class);
        return (int)$repository->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.commented = :post')
            ->andWhere('c.root IS NULL')
            ->setParameter('post', $post)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

//        if($commentsOrder === 'TOP') {
//            $qb->leftJoin('comment.reactions', 'reaction')
//                ->leftJoin('comment.replies', 'reply')
//                /* Благодаря следующим select можно легко отсортировать комменты по популярности, а самое главное, что комментарии запрашивающего
//                 * будут находиться в самом начале. Это происходит благодаря условию, если создатель коммента равен запрашивающеиу, то поле mainSort будет равно 1, иначе 0,
//                 * затем по этому полю будет происходить первая сортировка, то есть это главная сортировка, все записи, у которых mainSort === 1, будут вначале
//                 */
//                ->addSelect('(CASE WHEN comment.creator = :requester THEN 0 ELSE 1 END) AS HIDDEN mainSort')
//                ->addSelect('(COUNT(reaction) + COUNT(reply)) as HIDDEN reactions_and_replies_count')
//                ->groupBy('comment');
//        }