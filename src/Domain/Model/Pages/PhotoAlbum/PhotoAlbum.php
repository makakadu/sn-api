<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\PhotoAlbum;

use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Page\Page;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Laminas\EventManager\Exception\DomainException;
use Ulid\Ulid;
use Assert\Assertion;
use App\Domain\Model\Pages\Photos\AlbumPhoto\AlbumPhoto;

/*
 * В вк нельзя восстановить альбом и мне кажется, что это плохо, даже если удалить альбом нельзя одним нажатием, всё равно это можно сделать случайно.
 * Также меня смущает удаление модератором. Можна назвать альбом каким-то запрещённым словом или фразой, но там будут допустимые фото, стоит ли в таком случае удалять альбом и 
 * вместе с ним все фото? Я думаю, что не стоит заморачиваться и пока что разрешить мягко удалять альбом только владельцу, если это профиль, и "старшим" менеджерам, если это
 * страница или группа
 */
class PhotoAlbum {
    use EntityTrait;
    use \App\Domain\Model\Pages\PageEntity;
    
    const NAME_MAX_LENGTH = 50;
    const DESCRIPTION_MAX_LENGTH = 500;
    
    /** @var Collection <string, AlbumPhoto> $photos */
    protected Collection $photos;
    protected string $name;
    protected string $description;

    private bool $commentsAreDisabled;
    private bool $protected; // Только владелец, админы и редакторы могут добавлять фото

    protected bool $isDeleted = false;
    protected ?\DateTime $deletedAt = null;
            
    function __construct(User $creator, Page $page, string $name, string $description, bool $commentsAreDisabled, bool $protected) {
        $this->id = (string)Ulid::generate(true);
        $this->creator = $creator;
        $this->owningPage = $page;
        $this->photos = new ArrayCollection();
        
        $this->changeName($name);
        $this->changeDescription($description);
        
        $this->commentsAreDisabled = $commentsAreDisabled;
        $this->protected = $protected;
        
        $this->createdAt = new \DateTime('now');
        $this->deletedAt = new \DateTime('now');
    }
    
    public function name(): string {
        return $this->name;
    }
        
    function changeName(User $initiator, string $newName): void {
        if($this->isDeleted) {
            throw new DomainException("Cannot change name because album in trash");
        }
        if(!$this->owningPage->isAdminOrEditor($initiator)) {
            throw new DomainException("No rights to change name");
        }
        Assertion::maxLength(
            $newName, self::NAME_MAX_LENGTH,
            sprintf("Max length of description %s", self::NAME_MAX_LENGTH)
        );
        $this->name = $newName;
    }
    
    function changeDescription(User $initiator, string $newDescription): void {
        if($this->isDeleted) {
            throw new DomainException("Cannot change description because album in trash");
        }
        if(!$this->owningPage->isAdminOrEditor($initiator)) {
            throw new DomainException("No rights to change description");
        }
        Assertion::maxLength(
            $newDescription, self::DESCRIPTION_MAX_LENGTH,
            sprintf("Max length of description %s", self::DESCRIPTION_MAX_LENGTH)
        );
        $this->description = $newDescription;
    }
    
    /**
     * @param array<string> $versions
     * @throws DomainException
     */
    function addPhoto(User $creator, string $description, array $versions, bool $onBehalfOfPage): void {
        $page = $this->owningPage;
        if($this->isDeleted) {
            throw new DomainException("Cannot add photo because album in trash");
        }
        if($this->protected && !$page->isAdminOrEditor($creator)) {
            throw new DomainException("No rights to add photo");
        }
        if($this->owningPage->isBanned($creator)) {
            throw new DomainException("Banned users cannot add photo to album");
        }
        if($onBehalfOfPage && !$page->isAdminOrEditor($creator)) {
            throw new DomainException("No rights to add photo on behalf of page");
        }
        if($this->photos->count() >= 10000) {
            throw new DomainException("Max number of photos reached");
        }
        $photo = new AlbumPhoto($this, $creator, $description, $versions, $onBehalfOfPage);
        $this->photos->add($photo);
        $photo->changeAlbum($this);
    }
    
    /** @return Collection<string, AlbumPhoto> */
    function photos(): Collection {
        return $this->photos;
    }

    function delete(User $initiator): void {
        if($this->isDeleted) {
            throw new DomainException("Album already in trash");
        }
        if(!$this->owningPage->isAdminOrEditor($initiator)) {
            throw new DomainException("No rights to move album to trash");
        }
        $this->isDeleted = true;
        $this->deletedAt = new \DateTime('now');
    }

    function restore(User $initiator): void {
        if($this->isDeleted) {
            throw new DomainException("Album not in the trash");
        }
        if(!$this->owningPage->isAdminOrEditor($initiator)) {
            throw new DomainException("No rights to restore album");
        }
        $this->isDeleted = false;
        $this->deletedAt = null;
    }
    
    
    
}