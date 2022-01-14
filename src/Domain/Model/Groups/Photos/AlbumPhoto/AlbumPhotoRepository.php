<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Photos\AlbumPhoto;

interface AlbumPhotoRepository extends \App\Domain\Repository {
    function getById(string $id): ?AlbumPhoto;
}