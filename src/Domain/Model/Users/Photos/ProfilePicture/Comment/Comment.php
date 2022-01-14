<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Photos\ProfilePicture\Comment;

use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\DomainException;
use App\Domain\Model\Users\Comments\Attachment as CommentAttachment;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Users\Comments\ProfileComment;
use App\Domain\Model\Users\ProfileReaction;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Saveable;
use App\Domain\Model\Users\Photos\ProfilePicture\ProfilePicture;
use App\Domain\Model\Users\Comments\Reaction as ProfileCommentReaction;

class Comment extends ProfileComment {
    use \App\Domain\Model\Common\Comments\CommentTrait;
    
    private ProfilePicture $commented;

    function __construct(
        ProfilePicture $commentedPicture,
        User $creator,
        string $text,
        ?self $replied,
        ?CommentAttachment $attachment//,
        //?Page $asPage
    ) {
        parent::__construct($creator, $text, $commentedPicture->owner(), $attachment);//, $asPage);
        if($replied) {
            if(!$commentedPicture->equals($replied->commentedPicture())) {
                throw new \LogicException("Commentary on photo '{$commentedPicture->id()}' cannot be created as a reply to a comment that was left to another photo.");
            }
            if($replied->isRoot()) {
                $this->root = $replied;
                $this->repliedId = $replied->id();
            } else {
                $this->root = $replied->root();
                $this->repliedId = $replied->id();
            }
        }
        $this->commented = $commentedPicture;
        $this->reactions = new ArrayCollection();
        $this->replies = new ArrayCollection();
    }
    
    function commentedPicture(): ProfilePicture {
        return $this->commented;
    }

    function isRoot(): bool {
        return (bool)!$this->root;
    }
    
//    /** @return Collection<string, ProfileReaction> */
//    function reactions(): Collection {
//        /** @var ArrayCollection<string, ProfileReaction> $reactions */
//        $reactions = $this->reactions;
//        return $reactions;
//    }
//    
//    /** @return Collection<string, ProfileComment> */
//    function replies(): Collection {
//        /** @var ArrayCollection<string, ProfileComment> $replies */
//        $replies = $this->replies;
//        return $replies;
//    }
    
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
        return $visitor->visitProfilePictureComment($this);
    }

}
