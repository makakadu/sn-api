<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Photos\AlbumPhoto;

use App\Domain\Model\Common\DescribableTrait;
use App\Domain\Model\Common\PhotoTrait;
use App\Domain\Model\DomainException;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\Albums\Album;
use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Users\Photos\Photo;
use App\Domain\Model\Saveable;
use App\Domain\Model\Common\Reactable;
use App\Domain\Model\Common\Shares\Shareable;
use App\Domain\Model\Users\Photos\AlbumPhoto\Comment\Comment;
use App\Domain\Model\Users\Comments\ProfileComment;

class AlbumPhoto extends Photo implements Saveable, Shareable, Reactable {
    const DESCRIPTION_MAX_LENGTH = 300;
    
    use EntityTrait;
    use PhotoTrait;
    use DescribableTrait;
    
    private User $creator;
    
    /** @var Collection<string, Comment> $comments */
    private Collection $comments;
    
    private string $inAlbumId;
    private Album $album;
    private ?\DateTime $addedToAlbumAt = null;
            
    /** @param array<string> $versions */
    function __construct(User $creator, Album $album, array $versions) {
        $this->id = (string) \Ulid\Ulid::generate(true);
        $this->changeAlbum($album);
        $this->creator = $creator;
        $this->setVersions($versions);
        $this->createdAt = new \DateTime("now");
        $this->comments = new ArrayCollection();
        $this->reactions = new ArrayCollection();
    }
   
    
    function album(): Album {
        return $this->album;
    }
    
    function changeAlbum(Album $album): void {
        if($this->isDeleted || $this->isDeletedByGlobalManager) {
            throw new DomainException("Cannot change album if photo is softly deleted");
        }
        
        if(!$this->user->equals($album->user())) {
            throw new DomainException("Cannot add photo another's user album");
        }
        $this->album = $album;
        $this->addedToAlbumAt = new \DateTime("now");
        $this->inAlbumId = (string)\Ulid\Ulid::generate(true);
    }
    
    /** @return Collection<string, ProfileComment> */
    function comments(): Collection {
        /** @var Collection<string, ProfileComment> $comments */
        $comments = $this->comments;
        return $comments;
    }

    public function acceptSaveableVisitor(\App\Domain\Model\SaveableVisitor $visitor) {
        return $visitor->visitUserAlbumPhoto($this);
    }

    public function acceptReactableVisitor(\App\Domain\Model\Common\ReactableVisitor $visitor) {
        return $visitor->visitUserAlbumPhoto($this);
    }

}
