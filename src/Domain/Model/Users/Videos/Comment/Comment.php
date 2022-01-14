<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Videos\Comment;

use App\Domain\Model\Users\Photos\Photo;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Videos\Video;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use LogicException;
use App\Domain\Model\Users\Comments\ProfileComment;
use App\Domain\Model\Users\ProfileReaction;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Users\Comments\Attachment;
use App\Domain\Model\DomainException;

use App\Domain\Model\Users\Comments\Reaction as ProfileCommentReaction;

class Comment extends ProfileComment {
    use \App\Domain\Model\EntityTrait;
    use \App\Domain\Model\Common\Comments\CommentTrait;
    
    private Video $commented;
    
//    /** @var Collection<string, Reaction> $reactions */
//    private Collection $reactions;
//    /** @var Collection<string, Comment> $replies */
//    private Collection $replies;
//    
//    private ?self $root;
//    private ?string $repliedId;
    
    function __construct(
        Video $commented,
        User $creator,
        string $text,
        ?self $replied,
        ?Attachment $attachment,
        ?Page $asPage
    ) {
        parent::__construct($creator, $text, $commented->owner(), $attachment, $asPage);
        if($replied) {
            if(!$commented->equals($replied->commentedVideo())) {
                throw new LogicException("Commentary on photo '{$commented->id()}' cannot be created as a reply to a comment that was left to another photo.");
            }
            if($replied->isRoot()) {
                $this->root = $replied;
                $this->repliedId = $replied->id();
            } else {
                $this->root = $replied->root();
                $this->repliedId = $replied->id();
            }
        }
        $this->commented = $commented;
        $this->reactions = new ArrayCollection();
        $this->replies = new ArrayCollection();
    }
    
    function isRoot(): bool {
        return (bool)!$this->root;
    }

    public function owner(): User {
        return $this->owner;
    }
    
    function commentedVideo(): Video {
        return $this->commented;
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
    
    function react(User $reactor, string $type, ?Page $asPage): void {
        if($asPage && !$asPage->isAllowedForExternalActivity()) {
            throw new DomainException("Cannot react on behalf of given page because reacting in profiles is not allowed for this page");
        }
        if($asPage && !$asPage->isAdminOrEditor($reactor)) {
            throw new DomainException("No rights to react on behalf of given page");
        }
        
        /** @var ArrayCollection<string, ProfileReaction> $reactions */
        $reactions = $this->reactions;
        
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("creator", $reactor));
        if($reactions->matching($criteria)->count()) {
            throw new DomainException("User {$reactor->id()} already reacted");
        }
        $reaction = new ProfileCommentReaction($reactor, $this, $type, $asPage);
        $this->reactions->add($reaction);
    }
    
    public function rootId(): ?string {
        return $this->root ? $this->root->id : null;
    }

    
    public function repliesCount(): int {
        return $this->replies->count();
    }
    
    public function acceptProfileCommentVisitor(\App\Domain\Model\Users\Comments\ProfileCommentVisitor $visitor) {
        return $visitor->visitVideoComment($this);
    }

}
