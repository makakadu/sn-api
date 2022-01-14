<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Albums;

use App\Domain\Model\Users\User\User;

interface AlbumRepository extends \App\Domain\Repository {

    /** @return array<Album> */
    function getPart(User $owner, bool $areFriends, bool $haveCommonFriend, bool $inBlacklist, int $count, ?string $offsetId = '0'): array;
    function getById(string $id): ?Album;
    
    
}
