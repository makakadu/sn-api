<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Photos\GroupPicture;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Photos\Photo;
use App\Domain\Model\Groups\Group\Group;
use App\Domain\Model\Groups\Photos\GroupPicture\Comment\Comment;
use App\Domain\Model\Groups\Comments\Attachment as CommentAttachment;
use App\Domain\Model\DomainException;
use App\Application\Exceptions\ForbiddenException;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Saveable;
use App\Domain\Model\Common\Reactable;
use App\Domain\Model\Common\Shares\Shareable;
use App\Domain\Model\Groups\Comments\GroupComment;

class GroupPicture extends Photo implements Saveable, Shareable, Reactable {
    use \App\Domain\Model\EntityTrait;
    use \App\Domain\Model\Common\PictureTrait;
    
    private Group $group;
    private Photo $photo;    
    private \DateTime $updatedAt;
    
    /** @var Collection<string, Comment> $comments */
    private Collection $comments;
    
    /** @param array<string> $versions */
    function __construct(Group $group, Photo $photo, array $versions) {
        $this->group = $group;
        $this->photo = $photo;
        $this->setVersions($versions);
        $this->createdAt = new \DateTime('now');
        $this->updatedAt = new \DateTime('now');
        
        $this->comments = new ArrayCollection();
        $this->reactions = new ArrayCollection();
    }
    
    /** @return Collection<string, GroupComment> */
    function comments(): Collection {
        /** @var Collection<string, GroupComment> $comments */
        $comments = $this->comments;
        return $comments;
    }
    
    /** @param array<string> $versions */
    function edit(array $versions): void {
        $this->setVersions($versions);
    }
    
    public function comment(User $creator, string $text, ?string $repliedId, bool $asGroup, ?CommentAttachment $attachment): void {
        if($this->isSoftlyDeleted()) {
            throw new DomainException("Cannot comment because it is softly deleted");
        } elseif($asGroup && !$this->group->isAdminOrEditor($creator)) {
            throw new DomainException("Cannot comment on behalf of group");
        } elseif ($this->comments->count() >= 4000) {
            throw new ForbiddenException(111, "Max number(4000) of comments has been reached");
        }
        
        $replied = null;
        if($repliedId) {
            $replied = $this->comments->get($repliedId);
            if(!$replied) {
                throw new DomainException("Cannot replied to comment $repliedId, no such comment found");
            }
        }
        $comment = new Comment($this, $creator, $text, $replied, $asGroup, $attachment);
        $this->comments->add($comment);
    }
    
    public function acceptReactableVisitor(\App\Domain\Model\Common\ReactableVisitor $visitor) {
        return $visitor->visitGroupPicture($this);
    }

    public function acceptSaveableVisitor(\App\Domain\Model\SaveableVisitor $visitor) {
        return $visitor->visitGroupPicture($this);
    }
}