<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Videos;

use App\Domain\Model\Common\DescribableTrait;
use App\Domain\Model\Common\VideoTrait;
use App\Domain\Model\DomainException;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Pages\Videos\Comment\Comment;
use App\Domain\Model\Users\User\User;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Ulid\Ulid;
use App\Domain\Model\Pages\Comments\Attachment as CommentAttachment;
use App\Domain\Model\Saveable;
use App\Domain\Model\Common\Reactable;
use App\Domain\Model\Common\Shares\Shareable;
use App\Domain\Model\Pages\PageReaction;
use App\Domain\Model\Pages\Comments\PageComment;

class Video implements Saveable, Shareable, Reactable {
    use EntityTrait;
    use \App\Domain\Model\Pages\PageEntity;
    use VideoTrait;
    use DescribableTrait;
    
    const NAME_MAX_LENGTH = 50;
    const DESCRIPTION_MAX_LENGTH = 500;

    private string $name;
    
    private User $creator;
    private bool $onBehalfOfPage;
    private ?DateTime $deletedAt;
    
    private bool $offComments;
    
    /** @var Collection<string, Comment> $comments */
    private Collection $comments;
    /** @var Collection<string, PageReaction> $reactions */
    private Collection $reactions;
    
    /**
     * @param array<string> $previews
     */
    function __construct(Page $owningPage, User $creator, string $link, array $previews, bool $onBehalfOfPage) {
        $this->id = (string)Ulid::generate(true);
        
        $this->creator = $creator;
        $this->onBehalfOfPage = $onBehalfOfPage;
        $this->owningPage = $owningPage;
        
        $this->link = $link;
        
        $this->smallPreview = $previews['small'];
        $this->mediumPreview = $previews['medium'];
        $this->largePreview = $previews['large'];
        
        $this->comments = new ArrayCollection();
        $this->reactions = new ArrayCollection();
        
        $this->createdAt = new DateTime('now');
    }
    
    function name(): string {
        return $this->name;
    }
    
    function isSoftlyDeleted(): bool {
        return false;
    }
    
    function equals(self $video): bool {
        return $this->id === $video->id;
    }    
    
    function onBehalfOfPage(): bool {
        return $this->onBehalfOfPage;
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
    
    public function comment(User $creator, string $text, ?string $repliedId, ?CommentAttachment $attachment, ?Page $asPage): void {
        if($this->isSoftlyDeleted()) {
            throw new DomainException("Cannot comment because it is softly deleted");
        } elseif($this->offComments) {
            throw new DomainException("Comments are disabled");
        } elseif ($this->comments->count() >= 4000) {
            throw new DomainException("Max number(4000) of comments has been reached");
        } elseif($asPage && !$asPage->isAdminOrEditor($creator)) {
            throw new DomainException("No rights to comment on behalf of given page");
        } elseif($asPage && !$asPage->isAllowedForExternalActivity()) { /* Если страница не набрала достаточное число подписчиков, плохо оформлена и так далее, то внешняя активность запрещена */
            throw new DomainException("Cannot comment on behalf of given page because commenting in profiles is not allowed for this page");
        }
        $replied = null;
        if($repliedId) {
            $replied = $this->comments->get($repliedId);
            if(!$replied) {
                throw new DomainException("Cannot replied to comment $repliedId, no such comment found");
            }
        }
        $comment = new Comment($this, $creator, $text, $replied, $asPage, $attachment);
        $this->comments->add($comment);
    }
    
    /** @return Collection<string, PageReaction> */
    function reactions(): Collection {
        return $this->reactions;
    }
    
    /** @return Collection<string, PageComment> */
    function comments(): Collection {
        /** @var Collection<string, PageComment> $comments */
        $comments = $this->comments;
        return $comments;
    }
    
    public function acceptReactableVisitor(\App\Domain\Model\Common\ReactableVisitor $visitor) {
        return $visitor->visitPageVideo($this);
    }

    public function acceptSaveableVisitor(\App\Domain\Model\SaveableVisitor $visitor) {
        return $visitor->visitPageVideo($this);
    }

}
