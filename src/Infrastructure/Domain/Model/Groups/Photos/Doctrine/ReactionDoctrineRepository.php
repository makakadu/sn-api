<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Groups\Photos\Doctrine;

use App\Domain\Model\Groups\Photos\Reaction;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Groups\Photos\ReactionRepository;

class ReactionDoctrineRepository extends AbstractDoctrineRepository implements ReactionRepository {
    protected string $entityClass = Reaction::class;
}
