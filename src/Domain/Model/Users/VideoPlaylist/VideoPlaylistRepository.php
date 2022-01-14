<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\VideoPlaylist;

use App\Domain\Model\Users\User\User;

interface VideoPlaylistRepository extends \App\Domain\Repository {

    function getById(string $id): ?VideoPlaylist;
    
}
