<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Ban;

use App\Domain\Repository;

interface BanRepository extends Repository {
    function getById(string $id): ?Ban;
}
