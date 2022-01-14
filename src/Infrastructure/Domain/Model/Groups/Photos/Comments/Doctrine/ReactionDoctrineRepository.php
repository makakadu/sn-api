<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Groups\Photos\Comments\Doctrine;

use App\Domain\Model\Groups\Photos\Comments\Reaction;
use App\Domain\Model\Groups\Photos\Comments\ReactionRepository;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;

class ReactionDoctrineRepository extends AbstractDoctrineRepository implements ReactionRepository {
    protected string $entityClass = Reaction::class;


}
