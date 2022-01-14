<?php

declare(strict_types=1);

namespace App\Domain\Model\Dialogue;

use App\Domain\Repository;

interface DialogueRepository extends Repository {
    function getById(string $id): ?Dialogue;
}
