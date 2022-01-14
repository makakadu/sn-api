<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Videos\Comment;

use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Pages\Videos\Video;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Pages\Comments\PageComment;
use App\Domain\Model\Pages\Comments\Attachment;
use App\Domain\Model\Pages\PageReaction;
use App\Domain\Model\Pages\Comments\Reaction as PageCommentReaction;

class Comment extends PageComment {
    private Video $commented;
    private bool $onBehalfOfPage;
            
    function __construct(
        Video $commented,
        User $creator,
        string $text,
        ?self $replied,
        bool $onBehalfOfPage,
        ?Attachment $attachment
    ) {
        parent::__construct($creator, $text, $commented->owningPage(), $attachment);
        if($replied) {
            if(!$commented->equals($replied->commented)) {
                throw new \LogicException("Commentary on photo '{$commented->id()}' cannot be created as a reply to a comment that was left to another photo.");
            }
            if(!$replied->root) {
                $this->root = $replied;
                $this->repliedId = $replied->id;
            } else {
                $this->root = $replied->root();
                $this->repliedId = $replied->id;
            }
        }
        $this->onBehalfOfPage = $onBehalfOfPage;
        $this->commented = $commented;
        $this->reactions = new ArrayCollection();
        $this->replies = new ArrayCollection();
    }
            
    public function onBehalfOfPage(): bool {
        return $this->onBehalfOfPage;
    }
    
    function commentedVideo(): Video {
        return $this->commented;
    }

    function repliedId(): ?string {
        return $this->repliedId;
    }
    
    /** @return Collection<string, PageReaction> */
    function reactions(): Collection {
        /** @var ArrayCollection<string, PageReaction> $reactions */
        $reactions = $this->reactions;
        return $reactions;
    }
    
    /** @return Collection<string, PageComment> */
    function replies(): Collection {
        /** @var ArrayCollection<string, PageComment> $replies */
        $replies = $this->replies;
        return $replies;
    }
    
    function react(User $user, string $type, ?Page $asPage): PageCommentReaction {
        $reaction = new PageCommentReaction($user, $this, $type, $asPage);
        $this->reactions->add($reaction);
        return $reaction;
    }

    public function repliesCount(): int {
        return 0;
    }
    
    public function acceptPageCommentVisitor(\App\Domain\Model\Pages\Comments\PageCommentVisitor $visitor) {
        return $visitor->visitVideoComment($this);
    }

}
