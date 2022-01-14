<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Photos;

interface PhotoRepository extends \App\Domain\Repository {
    function getById(string $id): ?Photo;
}