<?php
declare(strict_types=1);
namespace App\Application;

interface TransactionalSession {
    /**
     * @param callable $operation
     * @return mixed
     */
    public function executeAtomically(callable $operation);
}