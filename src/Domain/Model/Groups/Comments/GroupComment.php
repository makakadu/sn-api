<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Comments;

use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Groups\Comments\Attachment;
use App\Domain\Model\Groups\Group\Group;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\DomainException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Groups\GroupReaction;
use App\Domain\Model\Common\Reactable;

abstract class GroupComment implements \App\Domain\Model\Common\Comments\Comment, Reactable {
    use \App\Domain\Model\EntityTrait;
    use \App\Domain\Model\Common\Comments\CommentTrait;
    
    protected ?\DateTime $deletedAt = null;
    protected bool $isDeleted = false;
    protected bool $isDeletedByLocalManager = false;
    protected bool $isDeletedByGlobalManager = false;
    
    protected ?Attachment $attachment;
    protected User $creator;
    protected Group $owningGroup;
    protected bool $onBehalfOfGroup;
    
    /** @var Collection<string, Reaction> $reactions */
    protected Collection $reactions;
    
    /** @var Collection<string, self> $replies */
    protected Collection $replies;
    
    protected ?self $root;
    protected ?string $repliedId;
    
    public function __construct(User $creator, Group $group, string $text, ?Attachment $attachment, bool $onBehalfOfGroup) {
        $this->id = (string)\Ulid\Ulid::generate(true);
        $this->creator = $creator;
        $this->text = $text;
        $this->owningGroup = $group;
        $this->onBehalfOfGroup = $onBehalfOfGroup;
        
        if($attachment) {
            $this->setAttachment($attachment);
        }
        $this->createdAt = new \DateTime("now");
    }
    
    public function attachment(): ?Attachment {
        return $this->attachment;
    }
        
    function onBehalfOfGroup(): bool {
        return $this->onBehalfOfGroup;
    }
    
    function owningGroup(): Group {
        return $this->owningGroup;
    }
    
    abstract function react(User $reactor, string $type, bool $asGroup): void;
//        if($asGroup && !$asGroup->isAdminOrEditor($reactor)) {
//            throw new DomainException("No rights to react on behalf of group");
//        }
//        
//        /** @var ArrayCollection<string, GroupReaction> $reactions */
//        $reactions = $this->reactions;
//        
//        $criteria = Criteria::create();
//        
//        if($asGroup) {
//            $criteria->where(Criteria::expr()->eq("asGroup", $asGroup));
//            if($reactions->matching($criteria)->count()) {
//                throw new DomainException("Already reacted on behalf of group {$asGroup->id()}");
//            }
//        } else {
//            $criteria->where(Criteria::expr()->eq("user", $reactor));
//            if($reactions->matching($criteria)->count()) {
//                throw new DomainException("User {$reactor->id()} already reacted ");
//            }
//        }
//        
//        if($reactions->matching($criteria)->count()) {
//            throw new DomainException("User {$reactor->id()} already reacted");
//        }
//        $reaction = new Reaction($reactor, $this, $type, $asGroup);
//        $this->reactions->add($reaction);
//    }
    
    function delete(User $user, bool $asLocalManager, bool $asGlobalManager): void {
        if($asLocalManager) {
            if($this->isDeletedByGlobalManager) {
                throw new DomainException("Cannot softly delete as group local manager because it already softly deleted by global manager");
            }
            if(!$this->owningGroup->isModer($user)) {
                throw new DomainException("Cannot softly delete as local manager");
            }
            if($this->isDeletedByLocalManager === false) {
                $this->deletedAt = new \DateTime('now');
            }
            $this->isDeletedByLocalManager = true;
        }
        elseif($asGlobalManager) {
            if(!$user->isGlobalManager()) {
                throw new DomainException("Cannot softly delete as global manager");
            }
            if($this->isDeletedByGlobalManager === false) {
                $this->deletedAt = new \DateTime('now');
            }
            $this->isDeletedByGlobalManager = true;
        }
        else {
            if($this->owningGroup->isPrivate() && !$this->owningGroup->isMemberOrManager($user)) {
                throw new DomainException("Cannot softly delete because access is forbidden");
            }
            if(!$this->creator->equals($user)) {
                throw new DomainException("Cannot softly delete because created by another member");
            }
            if($this->isDeleted === false) {
                $this->deletedAt = new \DateTime('now');
            }
            $this->isDeleted = true;
        }
    }
    
    
    function setAttachment(Attachment $attachment): void {
        $this->attachment = $attachment;
        $this->attachment->setComment($this);
    }
    
    /** @return Collection<string, self> */
    abstract function replies(): Collection;
    
    /** @return Collection<string, GroupReaction> */
    abstract function reactions(): Collection;
    
    function isDeletedByGlobalManager(): bool {
        return $this->isDeletedByGlobalManager;
    }
    
    function isDeleted(): bool {
        return $this->isDeleted;
    }
    
    abstract function repliesCount(): int;

    public function creator(): User {
        return $this->creator;
    }
    
    function root(): ?self {
        return $this->root;
    }

    function repliedId(): ?string {
        return $this->repliedId;
    }
    
    /**
     * @template T
     * @param \App\Domain\Model\Common\ReactableVisitor <T> $visitor
     * @return T
     */
    public function acceptReactableVisitor(\App\Domain\Model\Common\ReactableVisitor $visitor) {
        return $visitor->visitGroupComment($this);
    }
    
    /**
     * @template T
     * @param GroupCommentVisitor <T> $visitor
     * @return T
     */
    abstract function acceptGroupCommentVisitor(GroupCommentVisitor $visitor);
}
