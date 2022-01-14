<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\Playlists\Doctrine;

use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Users\Playlist\PlaylistRepository;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Playlists\VideoPlaylist;

class PlaylistDoctrineRepository extends AbstractDoctrineRepository implements VideoPlaylistRepository {
    protected string $entityClass = VideoPlaylist::class;

    public function getById(string $id): ?VideoPlaylist {
        return $this->entityManager->find($this->entityClass, $id);
    }

}