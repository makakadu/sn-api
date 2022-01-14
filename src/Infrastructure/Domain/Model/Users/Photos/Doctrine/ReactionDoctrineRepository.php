<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\Photos\Doctrine;

use App\Domain\Model\Users\Photos\Reaction;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Users\Photos\ReactionRepository;

class ReactionDoctrineRepository extends AbstractDoctrineRepository implements ReactionRepository {
    protected string $entityClass = Reaction::class;
    public function getById(string $id): ?Reaction {
        return $this->entityManager->find($this->entityClass, $id);
    }

}
