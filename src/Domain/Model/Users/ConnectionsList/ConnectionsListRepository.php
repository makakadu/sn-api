<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\ConnectionsList;

use App\Domain\Repository;

interface ConnectionsListRepository extends Repository {
    function getById(string $id): ?ConnectionsList;
    /** @param array<string> $ids
     * @return array<ConnectionsList>
     */
    function getByIds(array $ids): array;
}
