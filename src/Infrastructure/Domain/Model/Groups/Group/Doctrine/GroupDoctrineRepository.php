<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Groups\Group\Doctrine;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\ConflictException;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Domain\Model\Groups\Group\GroupRepository;
use App\Domain\Model\Groups\Group\Group;

class GroupDoctrineRepository extends AbstractDoctrineRepository implements GroupRepository {

    protected string $entityClass = Group::class;
//    
//    function flush() {
//        try {
//            parent::flush();
//        } catch (UniqueConstraintViolationException $e) {
//            throw new ConflictException($e->getMessage());
//        }
//    }

    public function getByName(string $name): ?Group {
        
    }

    public function getById(string $id): ?Group {
        return $this->entityManager->find($this->entityClass, $id);
    }

}
