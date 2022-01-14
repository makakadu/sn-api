<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Photos\Cover;

interface CoverRepository extends \App\Domain\Repository {
    function getById(string $id): ?Cover;
}