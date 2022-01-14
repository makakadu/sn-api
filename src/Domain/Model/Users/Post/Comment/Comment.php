<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Post\Comment;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Post\Post;
use App\Domain\Model\Users\Comments\ProfileComment;
use App\Domain\Model\Users\ProfileReaction;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Users\Comments\Attachment;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\DomainException;

use App\Domain\Model\Users\Comments\Reaction as ProfileCommentReaction;

class Comment extends ProfileComment {
    use \App\Domain\Model\EntityTrait;
    use \App\Domain\Model\Common\Comments\CommentTrait;
    
    private Post $commented;
//    
//    /** @var Collection<string, Reaction> $reactions */
//    private Collection $reactions;
//    /** @var Collection<string, self> $replies */
//    private Collection $replies;
//    
//    private ?self $root;
//    private ?string $repliedId;
        
//    private ?Page $onBehalfOfPage;
    
    function __construct(
        Post $commented, 
        User $creator, 
        string $text, 
        ?self $replied,
        ?Attachment $attachment,
        ?Page $onBehalfOfPage
    ) {
        parent::__construct($creator, $text, $commented->creator(), $attachment, $onBehalfOfPage);
        if($replied) {
            if(!$commented->equals($replied->commentedPost())) {
                throw new \LogicException("Passed comment from another post than. Passed in construct param 'commented' and property 'commented' of \$replied should be same");
            }
            if(!$replied->root()) {
                $this->root = $replied;
                $this->replied = $replied;
            } else {
                $this->root = $replied->root();
                $this->replied = $replied;
            }
        }
        $this->commented = $commented;
        $this->reactions = new ArrayCollection();
        $this->replies = new ArrayCollection();
        $this->createdAt = new \DateTime('now');
//        $this->onBehalfOfPage = $onBehalfOfPage;
    }
    
    function deleteReaction(string $reactionId): void {
        $reaction = $this->reactions->get($reactionId);
        $reaction->deleteFromComment();
        $this->reactions->remove($reactionId);
    }
    
    function restore(bool $asGlobalManager): void {
        if($asGlobalManager) {
//            if(!$this->isDeletedByGlobalManager) {
//                $this->deletedAt = new \DateTime("now");
//            }
//            $this->isDeletedByGlobalManager = true;
        } else {
//            if($this->isDeletedByGlobalManager) {
//                throw new \App\Domain\Model\DomainException("Already deleted by global manager");
//            }
            $this->deletedAt = null;
            $this->isDeleted = false;
        }
    }
    
    function delete(bool $asGlobalManager): void {
        if($asGlobalManager) {
            if(!$this->isDeletedByGlobalManager) {
                $this->deletedAt = new \DateTime("now");
            }
            $this->isDeletedByGlobalManager = true;
        } else {
            if($this->isDeletedByGlobalManager) {
                throw new \App\Domain\Model\DomainException("Already deleted by global manager");
            }
            if(!$this->isDeleted) {
                $this->deletedAt = new \DateTime("now");
            }
            $this->isDeleted = true;
        }
    }
    
    function commentedPost(): Post {
        return $this->commented;
    }

    function onBehalfOfPage(): ?Page {
        return $this->onBehalfOfPage;
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
    
    function react(User $reactor, int $type): ProfileReaction {//, ?Page $asPage): void {
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
        
        $matched = $reactions->matching($criteria);
        
        if($matched->count()) {
            throw new \App\Domain\Model\DomainExceptionAlt([
                'code' => \App\Application\Errors::ALREADY_REACTED,
                'message' => "User {$reactor->id()} already reacted to this comment",
                'reactionId' => $matched[0]->id()
            ]);
        }
        $reaction = new ProfileCommentReaction($reactor, $this, $type);//, $asPage);
        $this->reactions->add($reaction);
        return $reaction;
    }
    
    public function rootId(): ?string {
        return $this->root ? $this->root->id : null;
    }

    public function repliesCount(): int {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("isDeleted", 0))
            ->andWhere(Criteria::expr()->eq("isDeletedByGlobalManager", 0));
        return $this->replies->matching($criteria)->count();
    }
    
    public function acceptProfileCommentVisitor(\App\Domain\Model\Users\Comments\ProfileCommentVisitor $visitor) {
        return $visitor->visitPostComment($this);
    }

}
