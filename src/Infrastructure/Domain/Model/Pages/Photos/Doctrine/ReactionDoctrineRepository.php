<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Pages\Photos\Doctrine;

use App\Domain\Model\Pages\Photos\Reaction;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Pages\Photos\ReactionRepository;

class ReactionDoctrineRepository extends AbstractDoctrineRepository implements ReactionRepository {
    protected string $entityClass = Reaction::class;
    public function getById(string $id): ?Reaction {
        return $this->entityManager->find($this->entityClass, $id);
    }

}
