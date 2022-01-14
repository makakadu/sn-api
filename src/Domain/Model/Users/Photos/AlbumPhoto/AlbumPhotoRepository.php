<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Photos\AlbumPhoto;

use App\Domain\Model\Users\User\User;

interface AlbumPhotoRepository extends \App\Domain\Repository {
    
    /** @return array<AlbumPhoto> */
    function getPartOfAccessbileForRequesterByOwner(
        ?User $requester, User $owner, bool $hideFromPosts, bool $hideFromComments,
        bool $hideTemp, bool $hidePictures, ?string $offsetId, ?int $count
    ): array;
    
    function getById(string $id): ?AlbumPhoto;
    
}