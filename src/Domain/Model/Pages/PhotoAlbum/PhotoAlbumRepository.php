<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\PhotoAlbum;

use App\Domain\Model\Users\User\User;

interface PhotoAlbumRepository  {
    function getById(string $id): ?PhotoAlbum;
    /**
     * @return array<PhotoAlbum>
     */
    function getPart(User $owner, bool $areFriends, bool $haveCommonFriend, bool $inBlacklist, int $count, ?string $offsetId = '0'): array;
    
}
