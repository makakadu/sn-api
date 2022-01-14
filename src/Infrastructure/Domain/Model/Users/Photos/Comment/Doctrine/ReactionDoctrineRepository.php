<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\Photos\Comments\Doctrine;

use App\Domain\Model\Users\Photos\Comments\Reaction;
use App\Domain\Model\Users\Photos\Comments\ReactionRepository;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;

class ReactionDoctrineRepository extends AbstractDoctrineRepository implements ReactionRepository {
    protected string $entityClass = Reaction::class;

    public function getById(string $id): ?Reaction {
        return $this->entityManager->find($this->entityClass, $id);
    }

}
