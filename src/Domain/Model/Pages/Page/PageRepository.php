<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Page;

use App\Domain\Repository;

interface PageRepository extends Repository {
    function getById(string $id): ?Page;
    public function getByName(string $name): ?Page;
}
