<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Albums;

use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\Photos\AlbumPhoto\AlbumPhoto;
use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ulid\Ulid;
use Assert\Assertion;
use App\Domain\Model\DomainException;
use App\Domain\Model\Users\ConnectionsList\ConnectionsList;
use App\Domain\Model\Users\Connection\Connection;

class Album {
    use EntityTrait;
    
    const NAME_MAX_LENGTH = 50;
    const DESCRIPTION_MAX_LENGTH = 500;
    
    /** @var Collection <string, AlbumPhoto> $photos */
    protected Collection $photos;
    protected User $user;
    protected string $name;
    protected ?string $description = null;
    private PrivacySetting $whoCanSee;
    private PrivacySetting $whoCanComment;
    private ?\DateTime $deletedAt = null;
            
    /**
     * @param array<mixed> $whoCanSee
     * @param array<mixed> $whoCanComment
     */
    function __construct(User $user, string $name, string $description, array $whoCanSee, array $whoCanComment) {
        $this->id = (string)Ulid::generate(true);
        $this->user = $user;
        $this->photos = new ArrayCollection();
        
        $this->changeName($name);
        $this->changeDescription($description);
        
        $this->whoCanSee = new PrivacySetting(
            $this, 'who_can_see', $whoCanSee['access_level'], $whoCanSee['lists']
        );
        $this->whoCanComment = new PrivacySetting(
            $this, 'who_can_comment', $whoCanComment['access_level'], $whoCanComment['lists']
        );
        $this->createdAt = new \DateTime('now');
    }
    
    /** @param array<array> $lists */
    function changeWhoCanSee(int $accessLevel, array $lists): void {
        $this->whoCanSee->edit($accessLevel, $lists);
    }
    
    /** @param array<array> $lists */
    function changeWhoCanComment(int $accessLevel, array $lists): void {
        $this->whoCanComment->edit($accessLevel, $lists);
    }
    
    function whoCanSee(): PrivacySetting {
        return $this->whoCanSee;
    }

    function whoCanComment(): PrivacySetting {
        return $this->whoCanComment;
    }

    function name(): string {
        return $this->name;
    }
    
    function description(): ?string {
        return $this->description;
    }
            
    function isDeleted(): bool {
        return false;
    }
    
    /**
     * @param array<Connection> $allowedConnections
     * @param array<Connection> $unallowedConnections
     * @throws \LogicException
     */
    function failIfSameConnectionInBothLists(array $allowedConnections, array $unallowedConnections, string $protectableAction): void {
        $connectionsIntersections = array_intersect($allowedConnections, $unallowedConnections);
        if(count($connectionsIntersections)) {
            $id = $connectionsIntersections[array_key_first($connectionsIntersections)]; // первый элемент в массиве не обязательно с ключом 0, поэтому нужно узнать ключ первого элемента с помощью array_key_first(), чтобы получить первый элемент
            throw new \LogicException(
                "Connection ($id) cannot be at same time in list with connections that can "
                . "$protectableAction and in list with connections that cannot $protectableAction"
            );
        }
    }
    
    /**
     * @param array<ConnectionsList> $allowedLists
     * @param array<ConnectionsList> $unallowedLists
     * @throws \LogicException
     */
    function failIfSameConnectionsListInBothLists($allowedLists, $unallowedLists, string $protectableAction): void {
        $connectionsListsIntersections = array_intersect($allowedLists, $unallowedLists);
        if(count($connectionsListsIntersections)) {
            $id = $connectionsListsIntersections[array_key_first($connectionsListsIntersections)]->id();
            throw new \LogicException(
                "Connections list ($id) cannot be at same time in list with connections lists that can "
                . "$protectableAction and in list with connections lists that cannot $protectableAction"
            );
        }
    }
    
    function user(): User {
        return $this->user;
    }
        
    function changeName(string $name): void {
        Assertion::maxLength(
            $name, self::NAME_MAX_LENGTH,
            sprintf("Max length of description %s", self::NAME_MAX_LENGTH)
        );
        $this->name = $name;
    }
    
    function changeDescription(string $description): void {
        Assertion::maxLength(
            $description, self::DESCRIPTION_MAX_LENGTH,
            sprintf("Max length of description %s", self::DESCRIPTION_MAX_LENGTH)
        );
        $this->description = $description;
    }
    
    function addPhoto(AlbumPhoto $photo): void {
        /* Эта проверка смущает меня, потому что с одной стороны - это логическая ошибка, потому что есть чёткое правило, что в альбом можно добавить
         только фото, которые создал создатель альбома. Но с другой стороны это бизнес логика. И к тому же эта проверка здесь обязательна, чтобы
         соблюсти инварианты */
        if(!$this->user->equals($photo->owner())) {
            throw new DomainException("Cannot add to album photo of another user");
        }
        $this->photos->add($photo);
        $photo->changeAlbum($this);
    }
    
    /** @return ArrayCollection<string, AlbumPhoto> */
    function photos(): ArrayCollection {
        /** @var ArrayCollection<string, AlbumPhoto> $photos */
        $photos = $this->photos;
        return $photos;
    }
    
}