<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Pages\Ban\Doctrine;

use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Pages\Ban\BanRepository;
use App\Domain\Model\Pages\Ban\Ban;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Users\User\User;

class BanDoctrineRepository extends AbstractDoctrineRepository implements BanRepository {

    protected string $entityClass = Ban::class;
    
    public function getById(string $id): ?Ban {
        
    }

    function getByPageAndUser(Page $page, User $user): ?Ban {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb
            ->select('b')
            ->from($this->entityClass, 'b')
            ->where('b.page = :page')
            ->andWhere('b.banned = :banned')
            ->setParameters(array('page' => $page, 'banned' => $user))
            ->getQuery()
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')
            ->getSingleResult();
    }

}
