<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Post;

use App\Domain\Model\Common\PostTrait;
use App\Domain\Model\Common\PostVisitor;
use App\Domain\Model\Common\Shares\Shared;
use App\Domain\Model\DomainException;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Groups\Group\Group;
use App\Domain\Model\Users\User\User;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ulid\Ulid;
use App\Domain\Model\Groups\Post\Comment\Comment;
use App\Domain\Model\Groups\Comments\Attachment as CommentAttachment;
use App\Application\Exceptions\ForbiddenException;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Saveable;
use App\Domain\Model\Common\Reactable;
use App\Domain\Model\Common\Shares\Shareable;
use App\Domain\Model\Groups\GroupReaction;
use App\Domain\Model\Groups\Comments\GroupComment;

class Post implements Saveable, Shareable, Reactable {
    use EntityTrait;
    use \App\Domain\Model\Groups\GroupEntity;
    use PostTrait;
    
    const MEDIA_COUNT = 10;
    const TEXT_LENGTH = 300;
    
    protected bool $onBehalfOfGroup;
    private User $creator;
    private bool $addSignature; // Если true, то будет известно имя создателя поста, если пост создан от имени группы
    
    protected bool $offComments;
    
    private ?Shared $shared;
    
    /** @var Collection<string, Attachment> $attachments */
    private Collection $attachments;

    /** @var Collection<string, Reaction> $reactions */
    private Collection $reactions;
    /** @var Collection<string, Comment> $comments */
    private Collection $comments;
    
    private bool $isDeletedByLocalManager;
    private bool $isDeletedByGlobalManager;
    private bool $isDeleted;
    private ?DateTime $deletedByLocalManagerAt;
    private ?DateTime $deletedByGlobalManagerAt;
    private ?DateTime $deletedAt;
    
    /**
     * @param array<mixed> $attachments
     */
    function __construct(
        Group $owningGroup,
        User $creator,
        string $text,
        ?Shared $shared,
        bool $disableComments,
        array $attachments,
        bool $onBehalfOfGroup,
        bool $addSignature
    ) {
        $attachmentsCount = count($attachments);
        if($shared && $attachmentsCount > 1) {
            throw new DomainException('Only one attachment can be in post with shared');
        }
        elseif(!$shared && $attachmentsCount > 10) {
            throw new DomainException('Max 10 attachments can be in post');
        }
        elseif(!$onBehalfOfGroup && $addSignature) {
            throw new DomainException('If post is not created on behalf of group, then signature cannot be added to this post');
        }

        $this->id = Ulid::generate(true).'g';
        
        $this->owningGroup = $owningGroup;
        $this->creator = $creator;
        
        $this->onBehalfOfGroup = $onBehalfOfGroup;
        $this->addSignature = $addSignature;
        $this->offComments = $disableComments;
        
        $this->shared = $shared;
        $this->changeText($text);
        
        $this->comments = new ArrayCollection();
        $this->reactions = new ArrayCollection();
        $this->attachments = new ArrayCollection();

        $this->createdAt = new \DateTime('now');
        
        $this->setAttachments($attachments);
    }
    
    function showCreator(): bool {
        return $this->addSignature;
    }
    
    /**
     * @return Collection<string, Attachment>
     */
    function attachments(): Collection {
        return $this->attachments;
    }
    
    /**
     * @return Collection<string, GroupReaction>
     */
    function reactions(): Collection {
        /** @var Collection<string, GroupReaction> $reactions */
        $reactions = $this->reactions;
        return $reactions;
    }
    
    /**
     * @return Collection<string, \App\Domain\Model\Groups\Comments\GroupComment>
     */
    function comments(): Collection {
        /** @var Collection<string, \App\Domain\Model\Groups\Comments\GroupComment> $comments */
        $comments = $this->comments;
        return $comments;
    }
    
    function shared(): ?Shared {
        return $this->shared;
    }
    
    function creator(): User {
        return $this->creator;
    }
    
    
    /**
     * @param array<int, Attachment> $attachments
     * owningGroup, creator, shared, asGroup изменить нельзя
     * Будет проще, если клиент должен будет обязательно передать 3 параметра, тогда не нужно париться с операциями PATCH метод, их порядком и валидацией
     * Дело в том, что, например, для валидации текста нужно знать число прикреплений, если изменять каждое свойство отдельно, то это будет невозможно сделать, ведь может
     * быть такое, что клиент передал пустую строку для текста и несколько прикреплений, если сначала будет вызван метод changeText() и в посте ещё нет прикреплений, то 
     * будет выброшено исключение, потому что нельзя чтобы в посте не было текста и прикреплений одновременно. Но прикол в том, что клиент передал прикрепления, но
     * в методе changeText() об этом ничего неизвестно. Если же сначала будут добавлены прикрепления, а потому уже будет вызван changeText(), то всё будет ок. Вот так порядок
     * операций может вызвать ошибку, которой быть не должно
     */
    function edit(User $user, string $text, bool $offComments, array $attachments): void {
        /*
         * Параметер $user - это пользователь, который хочет отредачить пост
         */
        if(!$this->onBehalfOfGroup && !$this->creator->equals($user)) {
            throw new DomainException("Cannot edit post created by someone else");
        }
        if($this->onBehalfOfGroup && !$this->owningGroup->isAdminOrEditor($user)) {
            throw new DomainException("No rights to edit post created on behalf of group");
        }
        $offComments ? $this->disableComments() : $this->enableComments();
        $this->changeText($text);
        $this->setAttachments($attachments);
    }
    
    function disableComments(): void {
        if(!$this->onBehalfOfGroup) {
            throw new DomainException("Comments may be disabled or enabled only in post created on behalf of group");
        }
        $this->offComments = true;
    }
    
    function enableComments(): void {
        if(!$this->onBehalfOfGroup) {
            throw new DomainException("Comments may be enabled or disabled only in post created on behalf of group");
        }
        $this->offComments = false;
    }
        
    function equals(self $post): bool {
        return $this->id === $post->id;
    }
    
    function onBehalfOfGroup(): bool {
        return $this->onBehalfOfGroup;
    }
    
    /**
     * @param array<mixed> $attachments
     * @throws \InvalidArgumentException
     * 
     * Если пост создан НЕ от имени группы, то к этому посту может быть прикреплено только то фото, которое создано создателем этого поста
     * 
     * Если пост создан от имени группы, то его может редактировать любой админ или редактор. Это значит, что если к посту уже прикреплено какое-либо фото, то
     * неважно кто создатель этого фото. Но если же добавляется новое фото, которое ещё не связано с постом, то создателем этого фото должен быть тот, кто 
     * создаёт или редактирует пост, иначе будет ошибка
     * 
     * Если фото уже прикреплено к другому посту, то метод Photo::setPost() выбросит исключение
     */
    /**
     * @param array<int, Attachment> $attachments
     */
    private function setAttachments(array $attachments): void {
        /** @var Attachment $attachment */
        foreach ($attachments as $attachment) {
            if(!$this->creator->equals($attachment->creator())) {
                $attachmentType = $attachment->type();
                throw new DomainException("Cannot add $attachmentType attachment created by someone else");
            }
            $attachment->setPost($this);
        }
    }
    
    function comment(User $creator, string $text, ?string $repliedId, ?CommentAttachment $attachment, bool $onBehalfOfGroup): void {
        $wallSection = $this->owningGroup->wallSection();
        
        if($wallSection === 1) {// Если стена закрыта, то комменты создавать нельзя
            throw new DomainException("Cannot comment post when wall is restricted");
        }
        if($onBehalfOfGroup && !$this->owningGroup->isAdminOrEditor($creator)) { // чтобы комментировать пост от имени группы, нужно быть админом или редактором
            throw new DomainException("Cannot comment post on behalf of group");
        }
        if($this->offComments) {
            throw new ForbiddenException(111, "Cannot comment post because comments are disabled");
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
        $comment = new Comment($this, $creator, $text, $replied, $onBehalfOfGroup, $attachment);
        $this->comments->add($comment);
    }
    
    function react(User $user, string $type, bool $onBehalfOfGroup): void {
        /*
         * если группа приватная, то реагировать могут только участники и менеджеры
         * если же группа публичная, то реагировать может любой пользователь, даже перманентно забаненный
         */
        if($this->owningGroup->isPrivate() && !$this->owningGroup->isMemberOrManager($user)) {
            throw new \App\Domain\Model\DomainException('Post is unaccessible');
        }
        /*
         * чтобы реагировать на пост от имени группы, нужно быть админом или редактором
         */
        if($onBehalfOfGroup && !$this->owningGroup->isAdminOrEditor($user)) {
            throw new DomainException("No rights to react to post on behalf of group");
        }
        
        /** @var ArrayCollection<string, Reaction> $reactions */
        $reactions = $this->reactions;
        
        if($onBehalfOfGroup) {
            $criteria = Criteria::create()->where(Criteria::expr()->eq("onBehalfOfGroup", true));
            if($reactions->matching($criteria)->count()) {
                throw new DomainException("Reaction on behalf of group already created");
            }
        } else {
            $criteria = Criteria::create()
                ->where(Criteria::expr()->eq("creator", $user))
                ->andWhere(Criteria::expr()->eq("onBehalfOfGroup", false));
            if($reactions->matching($criteria)->count()) {
                throw new DomainException("User {$user->id()} already reacted to this post");
            }
        }

        $reaction = new Reaction($user, $this, $type, $onBehalfOfGroup);
        $this->reactions->add($reaction);
    }
    
    function delete(User $user, bool $asLocalManager, bool $asGlobalManager): void {
        if($asLocalManager) {
            if($this->isDeletedByGlobalManager) {
                throw new DomainException("Cannot softly delete as group local manager because it already softly deleted by global manager");
            }
            if(!$this->owningGroup->isModer($user)) {
                throw new DomainException("Cannot softly delete as local manager");
            }
            if($this->isDeletedByLocalManager === false) {
                $this->deletedByLocalManagerAt = new DateTime('now');
            }
            $this->isDeletedByLocalManager = true;
        }
        elseif($asGlobalManager) {
            if(!$user->isGlobalManager()) {
                throw new DomainException("Cannot softly delete as global manager");
            }
            if($this->isDeletedByGlobalManager === false) {
                $this->deletedByGlobalManagerAt = new DateTime('now');
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
                $this->deletedAt = new DateTime('now');
            }
            $this->isDeleted = true;
        }
    }
    
    function restore(User $user, bool $asLocalManager, bool $asGlobalManager): void {
        if($asGlobalManager) {
            if(!$user->isGlobalManager()) {
                throw new DomainException("Cannot to restore as global manager");
            }
            $this->deletedByGlobalManagerAt = null;
            $this->isDeletedByGlobalManager = false;
        }
        elseif($asLocalManager) {
            if($this->isDeletedByGlobalManager) {
                throw new DomainException("Cannot restore as group local manager because it already softly deleted by global manager");
            }
            if(!$this->owningGroup->isManager($user)) {
                throw new DomainException("Cannot to restore as group local manager");
            }
            $this->deletedByLocalManagerAt = null;
            $this->isDeletedByLocalManager = false;
        }
        else {
            if($this->owningGroup->isPrivate() && !$this->owningGroup->isMemberOrManager($user)) {
                throw new DomainException("Cannot restore because access is forbidden");
            }
            if(!$this->creator->equals($user)) {
                throw new DomainException("No rights to restore");
            }
            $this->deletedAt = null;
            $this->isDeleted = false;
        }
    }
    
    function isDeleted(): bool {
        return (bool)$this->deletedAt || (bool) $this->deletedByLocalManagerAt || (bool)$this->deletedByGlobalManagerAt;
    }

    /**
     * @return mixed
     */
    function accept(PostVisitor $postVisitor) {
        return $postVisitor->visitGroupPost($this);
    }
    
    function viewsCount(): int {
        return $this->viewsCount;
    }
    
    function acceptReactableVisitor(\App\Domain\Model\Common\ReactableVisitor $visitor) {
        return $visitor->visitGroupPost($this);
    }

    function acceptSaveableVisitor(\App\Domain\Model\SaveableVisitor $visitor) {
        return $visitor->visitGroupPost($this);
    }
}
