<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Ban;

use App\Domain\Repository;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Users\User\User;

interface BanRepository extends Repository {

    function getById(string $id): ?Ban;
    
    function getByPageAndUser(Page $page, User $user): ?Ban;
}
