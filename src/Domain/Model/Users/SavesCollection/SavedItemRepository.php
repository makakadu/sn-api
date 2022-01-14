<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\SavesCollection;

use App\Domain\Repository;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\SavesCollection\SavesCollection;

interface SavedItemRepository extends Repository {

    /** @return array<int,SavedItem> */
    function getPartByCollection(
        User $requester, string $collectionId,
        ?string $offsetId, int $count, string $order
    ): array;
}
