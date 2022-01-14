<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Groups\Membership\Doctrine;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\ConflictException;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Domain\Model\Groups\Group\GroupRepository;
use App\Domain\Model\Groups\Membership\Membership;
use App\Domain\Model\Groups\Membership\MembershipRepository;

class MembershipDoctrineRepository extends AbstractDoctrineRepository implements MembershipRepository {

    protected string $entityClass = Membership::class;


    public function getById(string $id): ?Membership {
        return $this->entityManager->find($this->entityClass, $id);
    }

}
