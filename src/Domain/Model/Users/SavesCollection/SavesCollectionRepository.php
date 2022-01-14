<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\SavesCollection;

use App\Domain\Repository;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\SavesCollection\SavesCollection;

interface SavesCollectionRepository extends Repository {
    function getById(string $id): ?SavesCollection;
    
    /**
     * @return array<int,SavesCollection>
     */
    function getPartOfActiveAndAccessibleToRequesterByOwner(User $requester, User $creator, int $count, string $order, ?string $offsetId): array;
}
