<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Videos;

use App\Domain\Model\Common\DescribableTrait;
use App\Domain\Model\Common\VideoTrait;
use App\Domain\Model\DomainException;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Comments\ProfileComment;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Ulid\Ulid;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Users\Videos\Comment\Comment;
use App\Domain\Model\Users\VideoPlaylist\VideoPlaylist;
use App\Domain\Model\Users\Comments\Attachment as CommentAttachment;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Saveable;
use App\Domain\Model\Common\Reactable;
use App\Domain\Model\Common\Shares\Shareable;
use App\Domain\Model\Users\ProfileReaction;
use Doctrine\Common\Collections\Criteria;

class Video implements \App\Domain\Model\Common\VideoInterface, Saveable, Shareable, Reactable {
    use EntityTrait;
    use VideoTrait;
    use DescribableTrait;
    
    const NAME_MAX_LENGTH = 50;
    const DESCRIPTION_MAX_LENGTH = 500;

    private User $owner;
    private User $creator;
    private DateTime $deletedAt;
    
    private string $name;
    
    private bool $offComments = false;
    
    /** @var Collection<string, Comment> $comments */
    private Collection $comments;
    /** @var Collection<string, ProfileReaction> $reactions */
    private Collection $reactions;
    
    private bool $isDeleted = false;
    private bool $isDeletedByGlobalManager = false;
    
    private PrivacySetting $whoCanSee;
    private PrivacySetting $whoCanComment;
    
    /**
     * @param array<mixed> $whoCanSee
     * @param array<mixed> $whoCanComment
     * @param array<string> $previews
     */
    function __construct(User $creator, string $name, string $description, string $link, string $hosting, array $previews, array $whoCanSee, array $whoCanComment) {
        $this->id = (string)Ulid::generate(true);
        $this->creator = $creator;
        $this->createdAt = new DateTime('now');
        
        $this->name = $name;
        $this->description = $description;
        $this->link = $link;
        $this->hosting = $hosting;
        
        $this->setPreviews($previews);
        
        $this->whoCanSee = new PrivacySetting($this, 'who_can_see', $whoCanSee['access_level'], $whoCanSee['lists']);
        $this->whoCanComment = new PrivacySetting($this, 'who_can_comment', $whoCanComment['access_level'], $whoCanComment['lists']);
        
        $this->comments = new ArrayCollection();
        $this->reactions = new ArrayCollection();
    }
    
    function isSoftlyDeleted(): bool {
        return false;
    }
    
    function react(User $reactor, int $type, ?Page $asPage): void {
//        if($this->disableReactions) {
//            throw new ForbiddenException(111, "Reactions are disabled");
//        }
        if($asPage && !$asPage->isAllowedForExternalActivity()) {
            throw new DomainException("Cannot react on behalf of given page because reacting in profiles is not allowed for this page");
        }
        if($asPage && !$asPage->isAdminOrEditor($reactor)) {
            throw new DomainException("No rights to react on behalf of given page");
        }
        
        /** @var ArrayCollection<string, Reaction> $reactions */
        $reactions = $this->reactions;
        
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("creator", $reactor));
        if($reactions->matching($criteria)->count()) {
            throw new DomainException("User {$reactor->id()} already reacted to this post");
        }
        $reaction = new Reaction($reactor, $this, $type, $asPage);
        $this->reactions->add($reaction);
    }
    
    function name(): string {
        return $this->name;
    }
    
    function addPlaylist(VideoPlaylist $playlist): void {
        //$this->playlists->add($playlist);
    }
    
    function equals(self $video): bool {
        return $this->id === $video->id;
    }
    
    /**
     * @return Collection <string, ProfileComment>
     */
    function comments(): Collection {
        /** @var Collection <string, ProfileComment> $comments */
        $comments = $this->comments;
        return $comments;
    }
    
    /** @return Collection<string, ProfileReaction> */
    function reactions(): Collection {
        return $this->reactions;
    }

    public function creator(): User {
        return $this->creator;
    }
    
    public function owner(): User {
        return $this->owner;
    }
    
    function isDeleted(): bool {
        return false;
    }
    
    public function changeDescription(string $description): void {
        $this->description = $description;
    }
    
    public function changeName(string $name): void {
        $this->changeName($name);
    }
    
    /**
     * @param array<mixed> $whoCanSee
     */
    public function changeWhoCanSee(array $whoCanSee): void {
//        if($this->commentId) {
//            throw new DomainException("Cannot change privacy settings('who_can_see') of video from comment, because it doesn't have privacy settings");
//        } elseif($this->post) {
//            throw new DomainException("Cannot change privacy settings('who_can_see') of video from post, because it doesn't have privacy settings");
//        } elseif($this->isTemp) {
//            throw new DomainException("Cannot change privacy settings('who_can_see') of temp video, because it doesn't have privacy settings");
//        }
//        $this->whoCanSee->edit($whoCanSee);
    }
    
    /**
     * @param array<mixed> $whoCanComment
     */
    public function changeWhoCanComment(array $whoCanComment): void {
//        if($this->commentId) {
//            throw new DomainException("Cannot change privacy settings('who_can_comment') of video from comment, because it doesn't have privacy settings");
//        } elseif($this->post) {
//            throw new DomainException("Cannot change privacy settings('who_can_comment') of video from post, because it doesn't have privacy settings");
//        } elseif($this->isTemp) {
//            throw new DomainException("Cannot change privacy settings('who_can_comment') of temp video, because it doesn't have privacy settings");
//        }
//        $this->whoCanSee->edit($whoCanComment);
    }
    
    public function comment(User $creator, string $text, ?string $repliedId, ?CommentAttachment $attachment, ?Page $asPage): void {
        if($this->isDeleted || $this->isDeletedByGlobalManager) {
            throw new DomainException("Cannot comment because it is softly deleted");
        } elseif($this->offComments) {
            throw new DomainException("Comments are disabled");
        } elseif ($this->comments->count() >= 4000) {
            throw new DomainException("Max number(4000) of comments has been reached");
        } elseif($asPage && !$asPage->isAdminOrEditor($creator)) {
            throw new DomainException("No rights to comment on behalf of given page");
        } elseif($asPage && !$asPage->isAllowedForExternalActivity()) { /* Если страница не набрала достаточное число подписчиков, плохо оформлена и так далее, то внешняя активность запрещена.
             * Это защита от страниц, которые созданы для сомнительных действий
             * Возможно стоит перенести в сервис авторизации по нескольким причинам:
             * 1. Возможно нужно будет использовать репозитории
             * 2. Код будет повторяться во многих местах
             */
            throw new DomainException("Cannot comment on behalf of given page because commenting in profiles is not allowed for this page");
        }
        $replied = null;
        if($repliedId) {
            $replied = $this->comments->get($repliedId);
            if(!$replied) {
                throw new DomainException("Cannot replied to comment $repliedId, no such comment found");
            }
        }
        $comment = new Comment($this, $creator, $text, $replied, $attachment, $asPage);
        $this->comments->add($comment);
    }
    
    public function whoCanSee(): PrivacySetting {
        return $this->whoCanSee;
    }
    
    public function whoCanComment(): PrivacySetting {
        return $this->whoCanComment;
    }


    public function acceptSaveableVisitor(\App\Domain\Model\SaveableVisitor $visitor) {
        return $visitor->visitUserVideo($this);
    }

    public function acceptReactableVisitor(\App\Domain\Model\Common\ReactableVisitor $visitor) {
        return $visitor->visitUserVideo($this);
    }

}
