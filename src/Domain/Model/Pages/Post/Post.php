<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Post;

use App\Domain\Model\Common\PostTrait;
use App\Domain\Model\Common\PostVisitor;
use App\Domain\Model\Common\Shares\Shared;
use App\Domain\Model\DomainException;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ulid\Ulid;
use App\Domain\Model\Pages\Post\Comment\Comment;
use App\Domain\Model\Pages\Comments\Attachment as CommentAttachment;
use App\Application\Exceptions\ForbiddenException;
use App\Domain\Model\Saveable;
use App\Domain\Model\Common\Reactable;
use App\Domain\Model\Common\Shares\Shareable;
use App\Domain\Model\Pages\PageReaction;
use App\Domain\Model\Pages\Comments\PageComment;
use Doctrine\Common\Collections\Criteria;

class Post extends AbstractPost implements Saveable, Shareable, Reactable  {
    use EntityTrait;
    use PostTrait;
    use \App\Domain\Model\Pages\PageEntity;
    
    const MEDIA_COUNT = 10;
    const TEXT_LENGTH = 300;
    
    private User $publisher;
    //private bool $disableComments;
    //private bool $disableReactions;
    //private int $viewsCount = 0;

    private ?Shared $shared;
    
    /** @var Collection<string, PageReaction> $reactions */
    private Collection $reactions;
    /** @var Collection<string, Comment> $comments */
    private Collection $comments;
    
    private bool $deletedByGlobalModeration = false;
    private ?\DateTime $deletedByGlobalModerationAt = null;
    
    /**
     * @param array<mixed> $attachments
     */
    function __construct(
        Page $owningPage,
        User $publisher,
        User $creator,
        string $text,
        ?Shared $shared,
        bool $disableComments,
        bool $disableReactions,
        array $attachments,
        bool $addSignature
    ) {
        $attachmentsCount = count($attachments);
        if($shared && $attachmentsCount > 1) {
            throw new DomainException('Only one attachment can be in post with shared');
        } elseif(!$shared && $attachmentsCount > 10) {
            throw new DomainException('Max 10 attachments can be in post');
        }
        parent::__construct($owningPage, $creator, $text, $attachments, $addSignature);
        $this->disableComments = $disableComments;
        $this->disableReactions = $disableReactions;
        $this->shared = $shared;
        
        $this->creator = $creator;
        $this->publisher = $publisher;
        
        $this->comments = new ArrayCollection();
        $this->reactions = new ArrayCollection();

        $this->createdAt = new \DateTime('now');
    }
    
    function isSoftlyDeleted(): bool {
        return $this->deleted || $this->deletedByGlobalModeration;
    }
    
    function commentsAreDisabled(): bool {
        return $this->disableComments;
    }
    
    function publisher(): User {
        return $this->publisher;
    }
    
    function viewsCount(): int {
        return $this->viewsCount;
    }
        
    function shared(): ?Shared {
        return $this->shared;
    }
        
    function equals(self $post): bool {
        return $this->id === $post->id;
    }
    
    function editReaction(User $initiator, string $reactionId, string $type): void {
        $reaction = $this->reactions->get($reactionId);
        if(!$reaction) {
            throw new \App\Domain\Model\DomainException("Reaction not found");
        }
        $reaction->edit($initiator, $type);
    }
    
    function deleteReaction(User $initiator, string $reactionId): void {
        $reaction = $this->reactions->get($reactionId);
        if(!$reaction) {
            throw new \App\Domain\Model\DomainException("Reaction not found");
        }
        if($reaction->onBehalfOfPage() && !$this->owningPage->isAdminOrEditor($initiator)) {
            throw new \App\Domain\Model\DomainException("No rights to delete reaction");
        }
        if(!$reaction->onBehalfOfPage() && !$reaction->creator()->equals($initiator)) {
            throw new \App\Domain\Model\DomainException("No rights to delete reaction created by another user");
        }
        
    }
    
    function comment(User $creator, string $text, ?string $repliedId, ?CommentAttachment $attachment, ?Page $onBehalfOfPage): void {
        if($this->disableComments) {
            throw new ForbiddenException(111, "Comments are disabled");
        } elseif ($this->comments->count() >= 4000) {
            throw new ForbiddenException(111, "Max number(4000) of comments has been reached");
        } elseif($this->owningPage->isBanned($creator)) { /* Создатель может быть менеджером, а менеджера нельзя забанить, но есть крохотный шанс того, что
         менеджер будет забанен, поэтому есть вероятность, что менеджер не сможет создать комментарий.
         */
            throw new ForbiddenException("Creator is banned");
        } 
        if($onBehalfOfPage) { /* Возможно стоит разрешить добавлять страницы в черный список */
            $owningPage = $this->owningPage;
            $isCurrentPage = $owningPage->equals($onBehalfOfPage);
            if(!$isCurrentPage && !$onBehalfOfPage->isAllowedForExternalActivity()) {
                throw new ForbiddenException("Creating comments on behalf of this page ({$onBehalfOfPage->id()}) on other pages is forbidden. This page in not allowed for external activity");
            }
            if(!$isCurrentPage && !$onBehalfOfPage->canCreatePromotedComments($creator)) {
                throw new ForbiddenException("No rights to create comments on behalf of page ({$onBehalfOfPage->id()}) on other pages");
            }
            if($isCurrentPage && !$owningPage->isAdminOrEditor($creator)) {
                throw new ForbiddenException("No rights to create comment on behalf of current page");
            }
        }
        $replied = null;
        if($repliedId) {
            $replied = $this->comments->get($repliedId);
            if(!$replied) {
                throw new DomainException("Cannot replied to comment $repliedId, no such comment found");
            }
        }
        $comment = new Comment($this, $creator, $text, $replied, $onBehalfOfPage, $attachment);
        $this->comments->add($comment);
    }
    
    /*
     * Я считаю, что не стоит разрешать создавать реакции от имени других страниц, достаточно только от той, где находится пост
     */
    function react(User $reactor, string $type, bool $onBehalfOfPage): void {
        if($this->disableReactions) {
            throw new ForbiddenException(111, "Reactions are disabled");
        }
        if($onBehalfOfPage && !$this->owningPage->isAdminOrEditor($reactor)) {
            throw new DomainException("No rights to react on behalf of current page");
        }
        if($this->owningPage->isBanned($reactor)) {
            throw new DomainException("Banned users cannot react");
        }
        /** @var ArrayCollection<string, Reaction> $reactions */
        $reactions = $this->reactions;
        
        if($onBehalfOfPage) {
            $criteria = Criteria::create()->where(Criteria::expr()->eq("onBehalfOfPage", true));
            if($reactions->matching($criteria)->count()) {
                throw new DomainException("Reaction on behalf of page already created");
            }
        } else {
            $criteria = Criteria::create()->where(Criteria::expr()->eq("creator", $reactor));
            if($reactions->matching($criteria)->count()) {
                throw new DomainException("User {$reactor->id()} already reacted to this post");
            }
        }
        $reaction = new Reaction($reactor, $this, $type, $onBehalfOfPage);
        $this->reactions->add($reaction);
    }
    
//    // Пост может быть создан только от имени страницы и его могут удалять только менеджеры страницы и глобальные менеджеры, если $byGlobalManager === true, то значит пост удаляется менеджером
//    // страницы
//    function delete(bool $asGlobalManager): void {
//        if($asGlobalManager) {
//            // Если пост уже мягко удалён менеджерами страницы, то он может быть также мягко удалён глобальными менеджерами, в таком случае менеджеры страницы не смогут восстановить
//            // пост, пока его не восстановят глобальные менеджеры.
//            $this->isDeletedByGlobalManager = true;
//            if(!$this->deletedByGlobalManagerAt) { // Это нужно, если перед этим свойство $isDeletedByGlobalManager уже было true, то есть в случае, если это свойство не изменяется
//                $this->deletedByGlobalManagerAt = new \DateTime('now');
//            }
//        } else {
//            // Если менеджер страницы хочет мягко удалить пост и пост уже мягко удалён глобальными менеджерами, то будет выброшено исключение.
//            if($this->isDeletedByGlobalManager) {
//                throw new DomainException("Cannot soft delete post because it softly deleted by global manager");
//            }
//            $this->isDeleted = true;
//        }
//        $this->deletedAt = new \DateTime('now');
//    }
//    
//    function restore(bool $asGlobalManager): void {
//        if($asGlobalManager) {
//            $this->isDeletedByGlobalManager = false;
//        } else {
//            if($this->isDeletedByGlobalManager) {
//                throw new DomainException("Cannot change property \$isDeleted to false while \$isDeletedByGlobalManager is true");
//            }
//            $this->isDeleted = false;
//        }
//    }

    // Эти методы вряд ли понядобятся, если изменение поста будет происходить только через edit()
//    function enableComments(): void { 
//        $this->disableComments = false;
//    }
//    
//    function disableComments(): void {
//        $this->disableComments = true;
//    }


    /* Казалось бы нужно только одно свойство для мягкого удаления. Но на самом деле это абсолютно не так. Первая причина почему одно свойство - это неудобно:
     * 1. Как это реализовать используя REST API?
     * 2. Невозможно реализовать без дополнительного свойства, где будет храниться инфа о том, как была удалёна сущность
     * 3. Без версионирования нельзя гарантировать что удаление глобальным модератором не будет затерто обычным удалением. Такое может быть, если не использовать блокировку. 2 процесса могут 
     * одновременно записать удаление и одно удаление может перезаписать другое, даже, если в коде куча проверок, это типичная проблема, которая возникает из-за того, что процессы
     * используют устаревшие на наносекунды данные.
     * 4. Если глобальный модератор по ошибке удалит пост пользователя, который уже удалён пользователем, а затем восстановит, то пост уже не будет удален ни глоб. модером ни пользо
     * вателем, хотя пользователю это не нужно. Такое может произойти запросто, модератор видит пост пользователя, нажимает кнопку удалить, а за секунду до этого его удалил владелец.
     * Модератор вроде как удалил активный пост, а по факту удалил пост, который уже удалён.
     * Мне не хватает опыта в этом деле, сейчас мне кажется самым лучшим вариантом - иметь несколько свойств как deleted, deletedByPageModeration, deletedByGlobalModeration
     */
    function delete(User $initiator): void {
        if(!$this->owningPage->isAdminOrEditor($initiator)) {
            throw new DomainException('No rights to delete post');
        }
        if(!$this->deleted) {
            $this->deletedAt = new \DateTime('now');
        }
        $this->deleted = true;
    }

    function deleteByGlobalModerator(User $initiator): void {
        if(!$initiator->isGlobalManager()) {
            throw new DomainException('Cannot delete post as global manager without being a global manager');
        }
        if($this->owningPage->isAdminOrEditor($initiator)) {
            throw new DomainException('Manager of current page cannot delete post as global manager');
        }
        if(!$this->deletedByGlobalModeration) {
            $this->deletedByGlobalModerationAt = new \DateTime('now');
        }
        $this->deletedByGlobalModeration = true;
    }
    
    function restore(User $initiator): void {
        if(!$this->owningPage->isAdminOrEditor($initiator)) {
            throw new DomainException('No rights to restore post');
        }
        $this->deleted = false;
        $this->deletedAt = null;
    }
    
    function restoreByGlobalManager(User $initiator): void {
        if(!$initiator->isGlobalManager()) {
            throw new DomainException('No rights to restore post deleted by global moderation without being a global manager');
        }
        $this->deletedByGlobalModeration = false;
        $this->deletedByGlobalModerationAt = null;
    }
    
    /**
     * @return Collection<string, PageComment>
     */
    function comments(): Collection {
        /** @var Collection<string, PageComment> $comments */
        $comments = $this->comments;
        return $comments;
    }
    
    /** @return Collection<string, PageReaction> */
    function reactions(): Collection {
        return $this->reactions;
    }
    
    public function acceptReactableVisitor(\App\Domain\Model\Common\ReactableVisitor $visitor) {
        return $visitor->visitPagePost($this);
    }

    public function acceptSaveableVisitor(\App\Domain\Model\SaveableVisitor $visitor) {
        return $visitor->visitPagePost($this);
    }
}
