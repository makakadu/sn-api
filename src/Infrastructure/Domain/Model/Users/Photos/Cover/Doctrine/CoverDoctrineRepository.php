<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\Photos\Cover\Doctrine;

use App\Domain\Model\Users\Photos\Cover\Cover;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Users\Photos\Cover\CoverRepository;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use Doctrine\ORM\EntityManager;

class CoverDoctrineRepository extends AbstractDoctrineRepository implements CoverRepository {
    protected string $entityClass = Cover::class;

    public function getById(string $id): ?Cover {
        return $this->entityManager->find($this->entityClass, $id);
    }


}
