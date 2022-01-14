<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Pages\Post\Doctrine;

use App\Domain\Model\Pages\Post\Post;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Pages\Post\PostRepository;
use App\Domain\Model\Pages\Page\Page;

class PostDoctrineRepository extends AbstractDoctrineRepository implements PostRepository {
    protected string $entityClass = Post::class;
    public function getById(string $id): ?Post {
        return $this->entityManager->find($this->entityClass, $id);
    }

    /** @return array<int, Post> */
    public function getPartOfNotDeletedByPage(Page $page, ?string $offsetId, int $count): array {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('post')
            ->from($this->entityClass, 'post')
            ->where('post.owningPage = :owningPage')
            ->andWhere('post.deleted = 0')
            ->andWhere('post.deletedByGlobalModeration = 0');
        if($offsetId) {
            $qb->andWhere('post.id > :offsetId');
            $qb->setParameters(array('owningPage' => $page, 'offsetId' => $offsetId));
        } else {
            $qb->setParameters(array('owningPage' => $page));
        }
        $query = $qb->setMaxResults($count)
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')
            ->getQuery();
        
        return $query->getResult();
    }

}
