<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Photos\AlbumPhoto;

use App\Domain\Model\Common\DescribableTrait;
use App\Domain\Model\Common\PhotoTrait;
use App\Domain\Model\DomainException;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Groups\PhotoAlbum\PhotoAlbum;
use App\Domain\Model\Groups\Group\Group;
use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Groups\Photos\Photo;
use App\Domain\Model\Groups\Photos\AlbumPhoto\Comment\Comment;
use App\Application\Exceptions\ForbiddenException;
use App\Domain\Model\Groups\Comments\Attachment as CommentAttachment;
use App\Domain\Model\Saveable;
use App\Domain\Model\Common\Reactable;
use App\Domain\Model\Common\Shares\Shareable;
use App\Domain\Model\Groups\Comments\GroupComment;

class AlbumPhoto extends Photo implements Saveable, Shareable, Reactable {
    const DESCRIPTION_MAX_LENGTH = 300;
    
    use EntityTrait;
    use \App\Domain\Model\Groups\GroupEntity;
    use PhotoTrait;
    use DescribableTrait;

    private PhotoAlbum $album;
    private bool $asGroup;
    
    /** @var Collection<string, Comment> $comments */
    private Collection $comments;

    
    private ?string $inAlbumId;
    private ?\DateTime $addedToAlbumAt = null;
    /**
     * @param array<string> $versions
     */
    function __construct(Group $owningGroup, User $creator, PhotoAlbum $album, array $versions, bool $asGroup) {
        $this->id = (string) \Ulid\Ulid::generate(true);
        $this->inAlbumId = (string) \Ulid\Ulid::generate(true);
        $this->owningGroup = $owningGroup;
        $this->creator = $creator;
        $this->album = $album;
        $this->asGroup = $asGroup;
        $this->setVersions($versions);
        
        $this->createdAt = new \DateTime("now");
        $this->comments = new ArrayCollection();
        $this->reactions = new ArrayCollection();
    }
    
    public function onBehalfOfGroup(): bool {
        return $this->asGroup;
    }
        
    /** @return Collection<string, GroupComment> */
    function comments(): Collection {
        /** @var Collection<string, GroupComment> $comments */
        $comments = $this->comments;
        return $comments;
    }
    
    function changeAlbum(PhotoAlbum $album): void {
        $this->album = $album;
    }

    /**
     * @return mixed
     */
    public function accept(\App\Domain\Model\Common\PhotoVisitor $visitor) {
        $visitor->visitGroupPhoto($this);
    }

    public function comment(User $creator, string $text, ?string $repliedId, bool $asGroup, ?CommentAttachment $attachment): void {
        if($this->isSoftlyDeleted()) {
            throw new DomainException("Cannot comment because it is softly deleted");
        } elseif($asGroup && !$this->owningGroup->isAdminOrEditor($creator)) {
            throw new DomainException("Cannot comment on behalf of group");
        } elseif($this->album->commentsAreDisabled()) {
            throw new ForbiddenException(111, "Cannot comment because comments are disabled");
        } elseif ($this->comments->count() >= 4000) {
            throw new ForbiddenException(111, "Max number(4000) of comments has been reached");
        }
        
        $replied = null;
        if($repliedId) {
            $replied = $this->comments->get($repliedId);
            if(!$replied) {
                throw new DomainException("Cannot replied to comment $repliedId, no such comment found");
            }
        }
        $comment = new Comment($this, $creator, $text, $replied, $asGroup, $attachment);
        $this->comments->add($comment);
    }

    public function acceptReactableVisitor(\App\Domain\Model\Common\ReactableVisitor $visitor) {
        return $visitor->visitGroupAlbumPhoto($this);
    }

    public function acceptSaveableVisitor(\App\Domain\Model\SaveableVisitor $visitor) {
        return $visitor->visitGroupAlbumPhoto($this);
    }
    
    function album(): PhotoAlbum {
        return $this->album;
    }

}
