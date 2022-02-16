<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Connection;

use App\Domain\Repository;
use App\Domain\Model\Users\Connection\Connection;
use App\Domain\Model\Users\User\User;

interface ConnectionRepository extends Repository {
    function getById(string $id): ?Connection;
    function getByUsersIds(string $user1Id, string $user2Id): ?Connection;
    function haveCommonFriend(string $user1Id, string $user2Id): bool;
    /**
     * @param array<string> $ids
     * @return array<Connection>
     */
    function getByIds(array $ids): array;
    
    /**
     * @return array<Connection>
     */
    function getWithUser(User $user, ?string $cursor, int $count, bool $hideAccepted, bool $hidePending, ?string $type, ?int $start): array;
    function getCountWithUser(User $user, bool $hideAccepted, bool $hidePending, ?string $type, ?int $start): int;


//    function getByInitiatorId(int $id);
//    function getBySecondId(int $id);
}
