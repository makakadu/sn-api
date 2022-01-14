<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\PhotoAlbum;

use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Group\Group;
use Assert\Assertion;
use Ramsey\Collection\Collection;
use Ulid\Ulid;
use App\Domain\Model\Groups\Photos\AlbumPhoto\AlbumPhoto;
use App\Domain\Model\DomainException;

class PhotoAlbum {
    use EntityTrait;
    use \App\Domain\Model\Groups\GroupEntity;
    
    const MAX_PHOTOS_COUNT = 5000;
    
    private string $name;
    private string $description;
    private bool $offComments;
    private bool $isRestricted; // Только админы и редакторы могут добавлять фото
    
    private User $creator;

    private bool $isDeleted = false;
    private ?\DateTime $deletedAt;
    
    /** @var Collection <string, AlbumPhoto> $photos */
    private Collection $photos;

    function __construct(User $creator, Group $group, string $name, string $description, bool $offComments, bool $isRestricted) {
        Assertion::maxLength($name, 50);
        Assertion::maxLength($description, 300);
        $this->changeName($name);
        $this->changeDescription($description);
        $this->id = (string)Ulid::generate(true);
        $this->offComments = $offComments;
        $this->isRestricted = $isRestricted;
        $this->owningGroup = $group;
        $this->creator = $creator;
    }
    
    function name(): string {
        return $this->name;
    }
    
    function commentsAreDisabled(): bool {
        return $this->offComments;
    }
    
    function changeDescription(string $description): void {
        Assertion::maxLength($description, 300);
        $this->description = $description;
    }

    function changeName(string $name): void {
        Assertion::maxLength($name, 50);
        $this->name = $name;
    }

    function id(): string { return $this->id;}
    
    function owningGroup(): Group { return $this->owningGroup;}

    function description(): string {
        return $this->description;
    }
    
    /**
     * @param array<string> $versions
     * @throws DomainException
     */
    function addPhoto(User $creator, array $versions, bool $asGroup): void {
        if($asGroup && !$this->owningGroup->isManager($creator)) {
            throw new DomainException("Cannot add on behalf of group");
        }
        if($this->isRestricted && !$asGroup) { // Если добавить фото в альбом могут только менеджеры, то $asGroup должен быть true
            throw new DomainException("If album is restricted then photo can be added only on behalf of group");
        }
        $photo = new AlbumPhoto($this->owningGroup, $creator, $this, $versions, $asGroup);
        $this->photos->add($photo);
    }

}
