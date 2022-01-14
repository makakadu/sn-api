<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Groups\Ban\Doctrine;

use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Groups\Ban\BanRepository;
use App\Domain\Model\Groups\Ban\Ban;
use App\Domain\Model\Groups\Group\Group;
use App\Domain\Model\Users\User\User;

class BanDoctrineRepository extends AbstractDoctrineRepository implements BanRepository {

    protected string $entityClass = Ban::class;
    
    public function getById(string $id): ?Ban {
        
    }

    public function getByGroupAndUser(Group $group, User $user): ?Ban {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb
            ->select('b')
            ->from($this->entityClass, 'b')
            ->where('b._group = :_group')
            ->andWhere('b.banned = :banned')
            ->setParameters(array('_group' => $group, 'banned' => $user))
            ->getQuery()
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')
            ->getSingleResult();
    }

}
