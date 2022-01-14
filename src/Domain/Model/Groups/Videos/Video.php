<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Videos;

use App\Domain\Model\Common\DescribableTrait;
use App\Domain\Model\Common\VideoTrait;
use App\Domain\Model\DomainException;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Groups\Videos\Comment\Comment;
use App\Domain\Model\Users\User\User;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Ulid\Ulid;
use App\Domain\Model\Groups\Group\Group;
use App\Domain\Model\Groups\Comments\Attachment as CommentAttachment;
use App\Domain\Model\Saveable;
use App\Domain\Model\Common\Reactable;
use App\Domain\Model\Common\Shares\Shareable;
use App\Domain\Model\Groups\GroupReaction;
use App\Domain\Model\Groups\Comments\GroupComment;

class Video implements Saveable, Shareable, Reactable {
    use EntityTrait;
    use \App\Domain\Model\Groups\GroupEntity;
    use VideoTrait;
    use DescribableTrait;
    
    const NAME_MAX_LENGTH = 50;
    const DESCRIPTION_MAX_LENGTH = 500;

    private string $name;
    
    private User $creator;
    private bool $asGroup;
    
    private ?DateTime $deletedAt;
    
    /** @var Collection<string, Comment> $comments */
    private Collection $comments;
    /** @var Collection<string, GroupReaction> $reactions */
    private Collection $reactions;
    
    private bool $isDeletedByMember = false;
    private bool $isDeletedByGroup = false;
    private bool $isDeletedByManager = false;
    
    /**
     * @param array<string> $previews
     */
    function __construct(
        Group $group,
        User $creator,
        string $link,
        string $hosting,
        string $name,
        string $description,
        array $previews,
        bool $asGroup
    ) {
        $this->id = (string)Ulid::generate(true);
        $this->creator = $creator;
        $this->owningGroup = $group;
        $this->asGroup = $asGroup;
        $this->changeName($name);
        $this->changeDescription($description);
        $this->link = $link;
        $this->hosting = $hosting;
        $this->smallPreview = $previews['small'];
        $this->mediumPreview = $previews['medium'];
        $this->largePreview = $previews['large'];
        $this->createdAt = new DateTime('now');
    }
    
    function name(): string {
        return $this->name;
    }
    
    public function onBehalfOfGroup(): bool {
        return $this->asGroup;
    }
        
    /**
     * @return Collection<string, GroupReaction>
     */
    function reactions(): Collection {
        return $this->reactions;
    }
    
    /**
     * @return Collection<string, \App\Domain\Model\Groups\Comments\GroupComment>
     */
    function comments(): Collection {
        /** @var Collection<string, \App\Domain\Model\Groups\Comments\GroupComment> $comments */
        $comments = $this->comments;
        return $comments;
    }
    
    public function creator(): User {
        return $this->creator;
    }
    
    public function changeDescription(string $description): void {
        $this->description = $description;
    }
    
    public function changeName(string $name): void {
        $this->changeName($name);
    }
    
    function isSoftlyDeleted(): bool {
        return false;
    }
    
    //public function comment(User $creator, string $text, ?string $repliedId, bool $onBehalfOfGroup, ?Photo $photo, ?self $video): void {
    public function comment(User $creator, string $text, ?string $repliedId, bool $asGroup, ?CommentAttachment $attachment): void {
        if($this->isSoftlyDeleted()) {
            throw new DomainException("Cannot comment because it is softly deleted");
        } elseif($asGroup && !$this->owningGroup->isAdminOrEditor($creator)) {
            throw new DomainException("Cannot comment on behalf of group");
        } elseif ($this->comments->count() >= 4000) {
            throw new DomainException("Max number(4000) of comments has been reached");
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
    
    function equals(self $video): bool {
        return $this->id === $video->id;
    }

    public function acceptReactableVisitor(\App\Domain\Model\Common\ReactableVisitor $visitor) {
        return $visitor->visitGroupVideo($this);
    }

    public function acceptSaveableVisitor(\App\Domain\Model\SaveableVisitor $visitor) {
        return $visitor->visitGroupVideo($this);
    }
    
}
