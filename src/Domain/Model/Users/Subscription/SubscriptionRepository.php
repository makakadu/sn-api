<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Subscription;

use App\Domain\Repository;

interface SubscriptionRepository extends Repository {
    function getById(string $id): ?Subscription;
    function getByUsersIds(string $subscriberId, string $userId): ?Subscription;
    function getOfUser(string $userId, ?string $cursor, int $count): array;
    function getCountOfUser(string $userId): int;
}
