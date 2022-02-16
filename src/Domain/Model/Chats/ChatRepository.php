<?php
declare(strict_types=1);
namespace App\Domain\Model\Chats;

use App\Domain\Repository;
use App\Domain\Model\Chats\Chat;
use App\Domain\Model\Users\User\User;

interface ChatRepository extends Repository {
    function getById(string $id): ?Chat;
    
    function getPartOfUser(User $user, ?string $interlocutorId, ?string $cursor, int $count, ?string $type): array;
    
}
