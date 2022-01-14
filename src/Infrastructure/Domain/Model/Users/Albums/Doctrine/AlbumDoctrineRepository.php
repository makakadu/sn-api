<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\Albums\Doctrine;

use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Users\Albums\AlbumRepository;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Albums\Album;

class AlbumDoctrineRepository extends AbstractDoctrineRepository implements PhotoAlbumRepository {
    protected string $entityClass = Album::class;

    public function getPart(User $owner, bool $areFriends, bool $haveCommonFriend, bool $inBlacklist, int $count, ?string $offsetId = '0'): array {
        
    }

    public function getCustomAlbums(User $owner) {
        
    }

    public function getById(string $id): ?Album {
        return $this->entityManager->find($this->entityClass, $id);
    }

}