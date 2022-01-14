<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Photos\AlbumPhoto\Comment;

use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Users\Photos\AlbumPhoto\AlbumPhoto;
use App\Domain\Model\DomainException;
use App\Domain\Model\Users\Comments\Attachment as CommentAttachment;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Users\Comments\ProfileComment;
use App\Domain\Model\Users\ProfileReaction;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Users\Comments\Reaction as ProfileCommentReaction;

class Comment extends ProfileComment {
    use \App\Domain\Model\Common\Comments\CommentTrait;
    
    private AlbumPhoto $commented;
    
    function __construct(
        AlbumPhoto $commentedPhoto,
        User $creator,
        string $text,
        ?self $replied,
        ?CommentAttachment $attachment
        //?Page $asPage
    ) {
        parent::__construct($creator, $text, $commentedPhoto->owner(), $attachment);// $asPage);
        if($replied) {
            if(!$commentedPhoto->equals($replied->commentedAlbumPhoto())) {
                throw new \LogicException("Commentary on photo '{$commentedPhoto->id()}' cannot be created as a reply to a comment that was left to another photo.");
            }
            if($replied->isRoot()) {
                $this->root = $replied;
                $this->repliedId = $replied->id();
            } else {
                $this->root = $replied->root();
                $this->repliedId = $replied->id();
            }
        }
        $this->commented = $commentedPhoto;
        $this->reactions = new ArrayCollection();
        $this->replies = new ArrayCollection();
    }
    
    function commentedAlbumPhoto(): AlbumPhoto {
        return $this->commented;
    }
    
    function isRoot(): bool {
        return (bool)!$this->root;
    }
    
    function react(User $reactor, string $type): void {//, ?Page $asPage): void {
//        if($asPage && !$asPage->isAllowedForExternalActivity()) {
//            throw new DomainException("Cannot react on behalf of given page because reacting in profiles is not allowed for this page");
//        }
//        if($asPage && !$asPage->isAdminOrEditor($reactor)) {
//            throw new DomainException("No rights to react on behalf of given page");
//        }
        
        /** @var ArrayCollection<string, ProfileReaction> $reactions */
        $reactions = $this->reactions;
        
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("creator", $reactor));
        if($reactions->matching($criteria)->count()) {
            throw new DomainException("User {$reactor->id()} already reacted");
        }
        $reaction = new ProfileCommentReaction($reactor, $this, $type);//, $asPage);
        $this->reactions->add($reaction);
    }

    public function repliesCount(): int {
        return $this->replies->count();
    }

    public function rootId(): ?string {
        return $this->root ? $this->root->id : null;
    }

    public function acceptProfileCommentVisitor(\App\Domain\Model\Users\Comments\ProfileCommentVisitor $visitor) {
        return $visitor->visitAlbumPhotoComment($this);
    }

}
