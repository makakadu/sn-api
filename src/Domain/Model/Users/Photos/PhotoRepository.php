<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Photos;

use App\Domain\Model\Users\User\User;

interface PhotoRepository extends \App\Domain\Repository {
    
    function getById(string $id): ?Photo;
    
}