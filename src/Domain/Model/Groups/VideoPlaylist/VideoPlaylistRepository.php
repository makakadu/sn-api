<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\VideoPlaylist;

interface VideoPlaylistRepository extends \App\Domain\Repository {

    function getById(string $id): ?VideoPlaylist;
    
}
