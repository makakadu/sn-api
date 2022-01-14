<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Photos\PagePicture\Comment;

use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Pages\PageReaction;
use App\Domain\Model\Pages\Comments\PageComment;
use App\Domain\Model\Pages\Comments\Attachment;
use App\Domain\Model\Pages\Photos\PagePicture\PagePicture;
use App\Domain\Model\Pages\Comments\Reaction as PageCommentReaction;

class Comment extends PageComment {
    private PagePicture $commented;
    private bool $onBehalfOfPage;
    
    function __construct(
        PagePicture $commented,
        User $creator,
        string $text,
        ?self $replied,
        bool $onBehalfOfPage,
        ?Attachment $attachment
    ) {
        parent::__construct($creator, $text, $commented->owningPage(), $attachment);
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
        $this->onBehalfOfPage = $onBehalfOfPage;
        $this->commented = $commented;
        $this->reactions = new ArrayCollection();
        $this->replies = new ArrayCollection();
    }
        
    public function onBehalfOfPage(): bool {
        return $this->onBehalfOfPage;
    }
    
    function equals(self $comment): bool {
        return $this->id === $comment->id;
    }
    
    function commentedPicture(): PagePicture {
        return $this->commented;
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
    
    function isRoot(): bool {
        return (bool)!$this->root;
    }

    public function repliesCount(): int {
        return 0;
    }
    
    public function acceptPageCommentVisitor(\App\Domain\Model\Pages\Comments\PageCommentVisitor $visitor) {
        return $visitor->visitPagePictureComment($this);
    }

}