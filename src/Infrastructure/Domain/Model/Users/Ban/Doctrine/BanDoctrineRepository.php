<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\Ban\Doctrine;

//use App\Domain\Model\Identity\User\User;
use App\Domain\Model\Users\Ban\Ban;
use App\Domain\Model\Users\Ban\BanRepository;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;

class BanDoctrineRepository extends AbstractDoctrineRepository implements BanRepository {

    protected string $entityClass = Ban::class;
    public function getById(string $id): ?Ban {
        return $this->entityManager->find($this->entityClass, $id);
    }

}
