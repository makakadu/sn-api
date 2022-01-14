<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Post\Comment;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Group\Group;
use App\Domain\Model\Groups\Post\Post;
use App\Domain\Model\Groups\Comments\Attachment;
use App\Domain\Model\Groups\Comments\GroupComment;
use App\Domain\Model\Groups\GroupReaction;
use App\Domain\Model\DomainException;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Groups\Comments\Reaction as GroupCommentReaction;

class Comment extends GroupComment {
    
    private Post $commented;
    
    function __construct(
        Post $commented,
        User $creator,
        string $text,
        ?self $replied,
        bool $onBehalfOfGroup,
        ?Attachment $attachment
    ) {
        parent::__construct($creator, $commented->owningGroup(), $text, $attachment, $onBehalfOfGroup);
        if($replied) {
            if(!$commented->equals($replied->commented)) {
                throw new \LogicException("Passed comment from another post than. Passed in construct param 'commented' and property 'commented' of \$replied should be same");
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
    
    /*
//    Этот метод был бы уместен, если бы была сущность Reply. На самом деле этот метод не особо делает код лучше и не делает его более подвергнутым ошибкам. 
//    function reply(User $creator, string $text, bool $asGroup, ?CommentPhoto $photo, ?CommentVideo $video): self {
//        if($this->replies->count() > 100) {
//            throw new DomainException("Number of replies to a comment {$this->id} has reached the maximum");
//        }
//        $reply = new self($this->commented, $creator, $text, $this, $asGroup, $photo, $video);
//        if($this->isRoot()) {
//            $this->replies->add($reply);
//        }
//        return $reply;
//    }
    */
    
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
        
    function equals(self $comment): bool {
        return $this->id === $comment->id;
    }
    
    function commentedPost(): Post {
        return $this->commented;
    }

//    function root(): ?self {
//        return $this->root;
//    }
//
//    function repliedId(): ?string {
//        return $this->repliedId;
//    }
    
    function react(User $reactor, string $type, bool $onBehalfOfGroup): void {
        
        /** @var ArrayCollection<string, GroupReaction> $reactions */
        $reactions = $this->reactions;
        $criteria = Criteria::create();
        
        if($onBehalfOfGroup) {
            if(!$this->owningGroup->isAdminOrEditor($reactor)) {
                throw new DomainException("No rights to react on behalf of group");
            }
            $criteria->where(Criteria::expr()->eq("onBehalfOfGroup", true));
            if($reactions->matching($criteria)->count()) {
                throw new DomainException("Already reacted on behalf of group {$this->owningGroup->id()}");
            }
        } else {
            $criteria->where(Criteria::expr()->eq("user", $reactor));
            if($reactions->matching($criteria)->count()) {
                throw new DomainException("User {$reactor->id()} already reacted ");
            }
        }
        $reaction = new GroupCommentReaction($reactor, $this, $type, $onBehalfOfGroup);
        $this->reactions->add($reaction);
    }
    
    function isRoot(): bool {
        return (bool)!$this->root;
    }

    public function repliesCount(): int {
        return 0;
    }

    public function creator(): User {
        return $this->creator;
    }
    
    public function acceptGroupCommentVisitor(\App\Domain\Model\Groups\Comments\GroupCommentVisitor $visitor) {
        return $visitor->visitPostComment($this);
    }

}
