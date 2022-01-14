<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Photos\GroupPicture\Comment;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Group\Group;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Groups\Photos\Photo;
use App\Domain\Model\Groups\Comments\Attachment;
use App\Domain\Model\Groups\Comments\GroupComment;
use App\Domain\Model\Groups\GroupReaction;
use App\Domain\Model\DomainException;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Groups\Photos\GroupPicture\GroupPicture;
use App\Domain\Model\Groups\Comments\Reaction as GroupCommentReaction;

class Comment extends GroupComment {
    use \App\Domain\Model\Groups\GroupEntity;
    
    private GroupPicture $commented;

    
    function __construct(
        GroupPicture $commented,
        User $creator,
        string $text,
        ?self $replied,
        bool $asGroup,
        ?Attachment $attachment
    ) {
        parent::__construct($creator, $commented->owningGroup(), $text, $attachment, $asGroup);
        if($replied) {
            if(!$commented->equals($replied->commentedPicture())) {
                throw new \LogicException("Commentary on photo '{$commented->id()}' cannot be created as a reply to a comment that was left to another photo.");
            }
            if($replied->isRoot()) {
                $this->root = $replied;
                $this->repliedId = $replied->id;
            } else {
                $this->root = $replied->root();
                $this->repliedId = $replied->id;
            }
        }
        $this->commented = $commented;
        $this->reactions = new ArrayCollection();
        $this->replies = new ArrayCollection();
    }
    
    function equals(self $comment): bool {
        return $this->id === $comment->id;
    }
    
    function commentedPicture(): GroupPicture {
        return $this->commented;
    }
    
    /** @return Collection<string, GroupReaction> */
    function reactions(): Collection {
        /** @var ArrayCollection<string, GroupReaction> $reactions */
        $reactions = $this->reactions;
        return $reactions;
    }
    
    /** @return Collection<string, GroupComment> */
    function replies(): Collection {
        /** @var ArrayCollection<string, GroupComment> $replies */
        $replies = $this->replies;
        return $replies;
    }
    
    function react(User $reactor, string $type, bool $asGroup): void {
        if($asGroup && !$this->owningGroup->isAdminOrEditor($reactor)) {
            throw new DomainException("No rights to react on behalf of group");
        }
        
        /** @var ArrayCollection<string, GroupReaction> $reactions */
        $reactions = $this->reactions;
        
        $criteria = Criteria::create();
        
        if($asGroup) {
            $criteria->where(Criteria::expr()->eq("asGroup", true));
            if($reactions->matching($criteria)->count()) {
                throw new DomainException("Already reacted on behalf of group {$this->owningGroup->id()}");
            }
        } else {
            $criteria->where(Criteria::expr()->eq("user", $reactor));
            if($reactions->matching($criteria)->count()) {
                throw new DomainException("User {$reactor->id()} already reacted ");
            }
        }
        $reaction = new GroupCommentReaction($reactor, $this, $type, $asGroup);
        $this->reactions->add($reaction);
    }
    
    function isRoot(): bool {
        return (bool)!$this->root;
    }

    public function repliesCount(): int {
        return $this->replies->count();
    }

    public function creator(): User {
        return $this->creator;
    }
    
    function repliedId(): ?string {
        return $this->repliedId;
    }
    
    public function acceptGroupCommentVisitor(\App\Domain\Model\Groups\Comments\GroupCommentVisitor $visitor) {
        return $visitor->visitGroupPictureComment($this);
    }
}
