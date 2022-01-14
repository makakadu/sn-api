<?php

declare(strict_types=1);

namespace App\Domain\Model\Dialogue;

use App\Domain\Repository;

interface MessagesHistoryRepository extends Repository {
    function getById(string $id): ?MessagesHistory;
}
