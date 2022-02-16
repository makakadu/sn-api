<?php
declare(strict_types=1);
namespace App\Domain\Model\Chats;

use App\Domain\Repository;

interface MessageRepository extends Repository {
    function getById(string $id): ?Message;
}
