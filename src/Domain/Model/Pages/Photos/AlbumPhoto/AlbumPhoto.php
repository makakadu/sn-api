<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Photos\AlbumPhoto;

use App\Domain\Model\Pages\PhotoAlbum\PhotoAlbum;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Page\Page;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Pages\Photos\AlbumPhoto\Comment\Comment;
use App\Domain\Model\Saveable;
use App\Domain\Model\Common\Reactable;
use App\Domain\Model\Common\Shares\Shareable;
use App\Domain\Model\Pages\Comments\PageComment;

class AlbumPhoto extends \App\Domain\Model\Pages\Photos\Photo implements Saveable, Shareable, Reactable {
    
    const DESCRIPTION_MAX_LENGTH = 300;
    
    private bool $onBehalfOfPage; // Фото не может быть создано от имени какой-то другой страницы, только от имени той, в которой находится
    
    /** @var Collection<string, Comment> $comments */
    private Collection $comments;
    
    private PhotoAlbum $album;
    private string $inAlbumId;
    private ?\DateTime $addedToAlbumAt = null;
            
    private bool $deletedByPageModeration = false;
    private ?\DateTime $deletedByPageModerationAt = null;
    
    /**
     * @param array<string> $versions
     */
    function __construct(PhotoAlbum $album, User $creator, string $description, array $versions, bool $onBehalfOfPage) {
        parent::__construct($creator, $album->owningPage(), $versions);
        $this->album = $album;
        $this->onBehalfOfPage = $onBehalfOfPage;
        $this->inAlbumId = (string) \Ulid\Ulid::generate(true);
        $this->addedToAlbumAt = $this->createdAt;
        $this->description = $description;
        
        $this->comments = new ArrayCollection();
        $this->reactions = new ArrayCollection();
    }
    
    function changeDescription(User $initiator, string $description): void {
        if($this->isSoftlyDeleted()) {
            throw new DomainException("Cannot change description because photo in trash");
        }
        if($this->album->isSoftlyDeleted()) {
            throw new DomainException("Cannot change description because album in trash");
        }
        if($this->onBehalfOfPage && !$this->owningPage->isAdminOrEditor($initiator)) {
            throw new DomainException("No rights to change description of photo created on behalf of page");
        }
        if(!$this->creator->equals($initiator)) {
            throw new DomainException("No rights to change description of photo created by another user");
        }
        $this->description = $description;
    }

    /**
     * @return Collection<string, PageComment>
     */
    function comments(): Collection {
        /** @var Collection<string, PageComment> $comments */
        $comments = $this->comments;
        return $comments;
    }
    
    function comment(User $creator, string $text, ?string $repliedId, ?CommentAttachment $attachment, bool $onBehalfOfPage): void {
        $page = $this->owningPage;
        
        if($this->isSoftlyDeleted()) {
            throw new DomainException("Cannot comment photo because it in trash");
        }
        if($this->album->isSoftlyDeleted()) {
            throw new DomainException("Cannot comment photo because album in trash");
        }
        elseif($this->disableComments) {
            throw new DomainException("Comments are disabled");
        }
        elseif ($this->comments->count() >= 4000) {
            throw new DomainException("Max number(4000) of comments has been reached");
        }
        elseif($page->isBanned($creator)) {
            throw new DomainException("Commenting is forbidden for banned users");
        }
        elseif($onBehalfOfPage && !$page->isAdminOrEditor($creator)) {
            throw new DomainException("No rights to comment on behalf of page");
        }

        $replied = null;
        if($repliedId) {
            $replied = $this->comments->get($repliedId);
            if(!$replied) {
                throw new DomainException("Cannot replied to comment $repliedId, no such comment found");
            }
        }
        $comment = new Comment($this, $creator, $text, $replied, $attachment, $onBehalfOfPage);
        $this->comments->add($comment);
    }
    
    function react(User $reactor, string $type, bool $asPage): void {
        if($asPage && !$this->owningPage->isAdminOrEditor($reactor)) {
            throw new DomainException("No rights to react on behalf of current page");
        }
        if($this->owningPage->isBanned($reactor)) {
            throw new DomainException("Banned users cannot react");
        }
        /** @var ArrayCollection<string, Reaction> $reactions */
        $reactions = $this->reactions;
        
        if($asPage) {
            $criteria = Criteria::create()->where(Criteria::expr()->eq("asPage", true));
            if($reactions->matching($criteria)->count()) {
                throw new DomainException("Reaction on behalf of page already created");
            }
        } else {
            $criteria = Criteria::create()->where(Criteria::expr()->eq("creator", $reactor));
            if($reactions->matching($criteria)->count()) {
                throw new DomainException("User {$reactor->id()} already reacted to this post");
            }
        }
        $reaction = new Reaction($reactor, $this, $type, $asPage);
        $this->reactions->add($reaction);
    }
    
    function isSoftlyDeleted(): bool {
        return ($this->isDeleted || $this->isDeletedByLocalManager || $this->isDeletedByGlobalManager);
    }
    
    public function onBehalfOfPage(): bool {
        return $this->onBehalfOfPage;
    }
            
    function album(): PhotoAlbum {
        return $this->album;
    }
    
    function changeAlbum(User $initiator, PhotoAlbum $album): void {
        if($this->onBehalfOfPage && !$this->owningPage->isAdminOrEditor($initiator)) {
            throw new DomainException("No rights to move photo created on behalf of page");
        }
        if(!$this->onBehalfOfPage && !$this->creator->equals($initiator)) {
            throw new DomainException("Cannot move photo created by another user or created on behalf of page");
        }
        if(!$this->owningPage->equals($album->owningPage())) {
            throw new DomainException("Cannot move photo to album from another page");
        }
        $this->album = $album;
    }
    
//    function delete(bool $asGlobalManager): void {
//        if($asGlobalManager) {
//            $this->isDeletedByGlobalManager = true;
//            if(!$this->deletedByGlobalManagerAt) { // Это нужно, если перед этим свойство $isDeletedByGlobalManager уже было true, то есть в случае, если это свойство не изменяется
//                $this->deletedByGlobalManagerAt = new \DateTime('now');
//            }
//        } else {
//            // Если менеджер страницы хочет мягко удалить пост и пост уже мягко удалён глобальными менеджерами, то будет выброшено исключение.
//            if($this->isDeletedByGlobalManager) {
//                throw new DomainException("Cannot soft delete post because it softly deleted by global manager");
//            }
//            $this->isDeleted = true;
//        }
//        $this->deletedAt = new \DateTime('now');
//    }
//    
//    function restore(bool $asGlobalManager): void {
//        if($asGlobalManager) {
//            $this->isDeletedByGlobalManager = false;
//        } else {
//            if($this->isDeletedByGlobalManager) {
//                throw new DomainException("Cannot change property \$isDeleted to false while \$isDeletedByGlobalManager is true");
//            }
//            $this->isDeleted = false;
//        }
//    }
    
    function delete(User $initiator): void {
        if($this->onBehalfOfPage && !$this->owningPage->isAdminOrEditor($initiator)) {
            throw new DomainException('Cannot delete album photo created on behalf of page');
        }
        if(!$this->onBehalfOfPage && !$this->creator->equals($initiator)) {
            throw new DomainException('Cannot delete album photo created by another user');
        }
        if(!$this->deleted) {
            $this->deletedAt = new \DateTime('now');
        }
        $this->deleted = true;
    }
    
    function deleteByPageModerator(User $initiator): void {
        if($this->onBehalfOfPage && !$this->owningPage->isAdminOrEditor($initiator)) {
            throw new DomainException('Album photo created on behalf of current page cannot be deleted by page moderation');
        }

        if(!$this->onBehalfOfPage && !$this->creator->equals($initiator)) {
            throw new DomainException('No rights to delete album photo created by another user');
        }
        if(!$this->deletedByPageModeration) {
            $this->deletedByPageModerationAt = new \DateTime('now');
        }
        $this->deletedByPageModeration = true;
    }
    
    public function acceptReactableVisitor(\App\Domain\Model\Common\ReactableVisitor $visitor) {
        return $visitor->visitPageAlbumPhoto($this);
    }

    public function acceptSaveableVisitor(\App\Domain\Model\SaveableVisitor $visitor) {
        return $visitor->visitPageAlbumPhoto($this);
    }

}
