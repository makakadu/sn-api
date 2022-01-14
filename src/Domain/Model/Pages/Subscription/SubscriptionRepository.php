<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Subscription;

use App\Domain\Repository;

interface SubscriptionRepository extends Repository {

    function getById(string $id): ?Subscription;
}
