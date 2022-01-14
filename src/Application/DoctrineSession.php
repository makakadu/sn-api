<?php
declare(strict_types=1);
namespace App\Application;

use Doctrine\ORM\EntityManager;

class DoctrineSession implements TransactionalSession {

    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function executeAtomically(callable $operation) {
        return $this->entityManager->transactional($operation);
    }

}
