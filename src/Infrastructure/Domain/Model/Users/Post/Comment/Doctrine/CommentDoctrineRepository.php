<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\Post\Comment\Doctrine;

use App\Domain\Model\Users\Post\Comment\CommentRepository;
use App\Domain\Model\Users\Post\Comment\Comment;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Users\Post\Post;
use App\Domain\Model\Users\User\User;

class CommentDoctrineRepository extends AbstractDoctrineRepository implements CommentRepository {
    protected string $entityClass = Comment::class;
    
    function getPart(string $postId, ?string $offsetId, int $limit): array {
        /** @var \Doctrine\ORM\QueryBuilder $qb */
        $qb = $this->entityManager->createQueryBuilder();
        $qb ->select('comment')
            ->from($this->entityClass, 'comment')
            ->where(
                    '(comment.post = :postId AND comment.replies IS NOT EMPTY)'
                  . 'OR (comment.post = :postId AND comment.deletedAt IS NULL)')
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

        $query = $qb->getQuery();
        return $query->getResult();
    }
    
    function getCountOfActiveByPost(Post $post): int {
        $repository = $this->entityManager->getRepository(Comment::class);
        return (int)$repository->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.commented = :post')
            ->andWhere('c.root IS NULL')
            ->andWhere('c.isDeleted = 0')
            ->andWhere('c.isDeletedByGlobalManager = 0')
            ->setParameter('post', $post)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getCountOfActiveByRootComment(Comment $comment): int {
        $repository = $this->entityManager->getRepository(Comment::class);
        return (int)$repository->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.root = :comment')
            ->andWhere('c.isDeleted = 0')
            ->andWhere('c.isDeletedByGlobalManager = 0')
            ->setParameter('comment', $comment)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getPartOfActiveByRootComment(Comment $comment, ?string $offsetId, int $count): array {
        $qb = $this->entityManager->getRepository(Comment::class)
            ->createQueryBuilder('comment');

        $qb->where('comment.root = :comment')
            ->andWhere('comment.isDeleted = 0');
        if($offsetId) {
            $qb->andWhere('comment.id < :offsetId');
            $qb->setParameter('offsetId', $offsetId);
        }
        $qb->setParameter('comment', $comment);
        $qb->setMaxResults($count);
        $qb->orderBy('comment.id', 'DESC');
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')
        $query = $qb->getQuery();
        return $query->getResult();
    }

}