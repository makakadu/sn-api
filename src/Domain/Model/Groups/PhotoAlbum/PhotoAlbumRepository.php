<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\PhotoAlbum;

use App\Domain\Repository;
use App\Domain\Model\Groups\PhotoAlbum\PhotoAlbum;

interface PhotoAlbumRepository extends Repository {
    function getById(string $id): ?PhotoAlbum;
}
