<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Post\Comment;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Post\Post;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Pages\Comments\PageComment;
use App\Domain\Model\Pages\Comments\Attachment;
use App\Domain\Model\Pages\PageReaction;
use App\Domain\Model\Pages\Comments\Reaction as PageCommentReaction;

class Comment extends PageComment {
    private Post $commented;
    private ?Page $onBehalfOfPage;
    
    function __construct(
        Post $commented,
        User $creator,
        string $text,
        ?self $replied,
        ?Page $onBehalfOfPage,
        ?Attachment $attachment
    ) {
        parent::__construct($creator, $text, $commented->owningPage(), $attachment);
        if($replied) {
            if(!$commented->equals($replied->commentedPost())) {
                throw new \LogicException("Passed comment from another post than. Passed in construct param 'commented' and property 'commented' of \$replied should be same");
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
    
    function commentedPost(): Post {
        return $this->commented;
    }

    function repliedId(): ?string {
        return $this->repliedId;
    }
    
    function onBehalfOfPage(): ?Page {
        return $this->onBehalfOfPage;
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
    
    function react(User $reactor, string $type, bool $onBehalfOfPage): PageCommentReaction {
        if($onBehalfOfPage && !$this->owningPage->isAdminOrEditor($reactor)) {
            throw new DomainException("No rights to react on behalf of current page");
        }
        if($this->owningPage->isBanned($reactor)) {
            throw new DomainException("Banned users cannot react");
        }
        $reaction = new PageCommentReaction($reactor, $this, $type, $onBehalfOfPage);
        $this->reactions->add($reaction);
        return $reaction;
    }
    
    function edit(User $initiator, string $text, ?Attachment $attachment) {
//        if($this->commented->commentsAreDisabled()) {
//            throw new DomainException("Cannot edit comment if comments are disabled");
//        }
        
        if($this->onBehalfOfPage) {
            $onBehalfOfCurrentPage = $this->onBehalfOfPage->equals($this->owningPage);
            
            if($onBehalfOfCurrentPage && !$this->owningPage->isAdminOrEditor($initiator)) {
                throw new DomainException("No rights to edit comment");
            } elseif(!$onBehalfOfCurrentPage && !$this->onBehalfOfPage->isAllowedForExternalActivity($initiator)) {
                throw new DomainException("No rights to edit comment");
            }
        }
        else {
            if(!$this->creator->equals($initiator)) {
                throw new \App\Domain\Model\DomainException("No rights to edit comment created by another user");
            }
        }
        if($attachment) {
            if($attachment->isDeleted()) {
                throw new \App\Domain\Model\DomainException("Cannot add attachment '{$attachment->id()}' because it in trash");
            }
            if($attachment->commentId() && $attachment->commentId() !== $this->id()) {
                throw new \App\Domain\Model\DomainException("Attachment '{$attachment->id()}' already added to another comment");
            }
        }
        if($this->attachment) {
            $this->attachment->detach();
        }
        $this->changeText($text);
        $this->attachment = $attachment;
        $attachment->attach($this);
    }
    
    function editReaction(User $initiator, string $reactionId, string $type): void {
        $reaction = $this->reactions->get($reactionId);
        if(!$reaction) {
            throw new \App\Domain\Model\DomainException("Reaction not found");
        }
        $reaction->edit($initiator, $type);
    }
    
    function equals(self $comment): bool {
        return $this->id === $comment->id;
    }

    public function repliesCount(): int {
        return 0;
    }
    
    public function acceptPageCommentVisitor(\App\Domain\Model\Pages\Comments\PageCommentVisitor $visitor) {
        return $visitor->visitPostComment($this);
    }
    
    function moveToTrash(User $initiator): void {
        if($this->onBehalfOfPage && !$this->owningPage->isManager($initiator)) {
            throw new DomainException('No rights to move comment created on behalf of page to recycle bin');
        }
        if(!$this->onBehalfOfPage && !$this->creator->equals($initiator)) {
            throw new DomainException('No rights to move comment created by another user to recycle bin');
        }
        if(!$this->inRecycleBin) {
            $this->movedToRecycleBin = new \DateTime('now');
        }
        $this->inRecycleBin = true;
    }
    
    /*
     * Я выбрал такие названия(isDeletedAsInappropriateByPageManager, isDeletedAsInappropriateByGlobalManager) из-за того, что isDeletedByPageManager никак не подходит. Дело в том
     * что это название не отображает суть. Когда в названии присутствует AsInappropriate, то понятно, что коммент был удалён из-за неподходящего содержания. А без этого в названии
     * непонятно по какой причине он был удален, возможно из-за неподходящего названия, а может из-за того, что менеджер создал коммент, а потом решил его удалить из-за того, что
     * посчитал его тупым, например, или может он вызвал у него кринж, но не из-за того, что он неподходящий, не из-за того, что нарушает правила и не из-за того, что содержит мат
     * или оскорбления.
     * В то же время isDeletedByGlobalManager очень даже уместно, потому что есть только одна причина по которой коммент может быть удалён глобальным менеджером.
     */
    
    /* Это метод который мягко удаляет пост. Это обычное удаление. Если пост создан от имени пользователя, то никто кроме него не может менять isDeleted. Если от имени страницы,
     * то это свойство может изменить владелец, админы и редакторы.
     */
    function delete(User $initiator): void {
        if($this->onBehalfOfPage) {
            $createdOnBehalfOfCurrentPage = $this->owningPage->equals($this->onBehalfOfPage);
        
            if($createdOnBehalfOfCurrentPage && !$this->owningPage->isAdminOrEditor($initiator)) {
                throw new DomainException('No rights to delete');
            }
            if(!$createdOnBehalfOfCurrentPage && (!$this->onBehalfOfPage->isManager($initiator) || !$this->onBehalfOfPage->isAllowedForExternalActivity($initiator))) {
                throw new DomainException('No rights to delete');
            }
        }
        if(!$this->onBehalfOfPage && !$this->creator->equals($initiator)) {
            throw new DomainException('No rights to delete comment created by another user');
        }
        if(!$this->isDeleted) {
            $this->deletedAt = new \DateTime('now');
        }
        $this->isDeleted = true;
    }
    
    
    
    function deleteByPageModerator(User $initiator): void {
        if(!$this->owningPage->isManager($initiator)) {
            throw new DomainException('Cannot delete comment as page moderator without being a page moderator');
        }
        if($this->onBehalfOfPage && $this->onBehalfOfPage->equals($this->owningPage)) {
            throw new DomainException('Comment created on behalf of current page cannot be deleted by current page moderation');
        }
//        if(!$this->onBehalfOfPage && $this->creator->equals($initiator)) {
//            throw new DomainException('Cannot delete comment created on its own behalf as page moderator');
//        }
        if(!$this->deletedByPageModeration) {
            $this->deletedByPageModerationAt = new \DateTime('now');
        }
        $this->deletedByPageModeration = true;
    }
    
    /*
     * Мне кажется, что глобальные модераторы не должны просматривать все комментарии, а должны просматривать только те комментарии, на которые пожаловались или которые содержат запрещенные
     * слова, например. 
     */
//    function deleteByGlobalModerator(User $initiator): void {
//        if(!$initiator->isGlobalManager()) {
//            throw new DomainException('Cannot delete comment as global moderator without being a global moderator');
//        }
//        if(!$this->isDeletedByGlobalModeration) {
//            $this->movedToRecycleBin = new \DateTime('now');
//        }
//        $this->violateCommunityRules = true;
//    }
    
    function restoreFromTrash(User $initiator): void {
        if($this->onBehalfOfPage && !$this->owningPage->isManager($initiator)) {
            throw new DomainException('No rights to restore comment from recycle bin');
        }
    }
    
    function moveToManagersTrash(User $initiator): void {
        if(!$this->isManager($initiator)) {
            throw new DomainException('No rights to move comment to recylce bin for page managers');
        }
        if(!$this->inManagersBin) {
            $this->movedToManagersBin = new \DateTime('now');
        }
        $this->inManagersBin = true;
    }
    
    function moveToGlobalTrash(User $initiator): void {
        if(!$this->isManager($initiator)) {
            throw new DomainException('No rights to move comment to recylce bin for page managers');
        }
        if(!$this->inManagersBin) {
            $this->movedToTrashAt = new \DateTime('now');
        }
        $this->inTrash = true;
    }

    function deleteByUser(User $initiator): void {
        if($this->onBehalfOfPage) {
            throw new DomainException('Cannot softly delete comment created on behalf of group as simple user');
        }
        if(!$this->creator->equals($initiator)) {
            throw new DomainException('No rights to move comment created by another user to trash');
        }
        if(!$this->isDeletedByCreator) {
            $this->deletedByCreator = new \DateTime('now');
        }
        $this->isDeletedByCreator = true;
    }

    function deleteByPageManager(User $initiator): void {
        if(!$this->owningPage->isManager($initiator)) {
            throw new DomainException('No rights to softly delete post as page manager');
        }
        /* Мне кажется, что если пользователь создал на странице коммент от своего имени, то нельзя удалять такой коммент */
        if($this->creator->equals($initiator)) {
            throw new DomainException('No rights to softly delete post created by himself as page manager');
        }
        if(!$this->isDeletedByPageManager) {
            $this->deletedByPageManager = new \DateTime('now');
        }
        $this->isDeletedByPageManager = true;
    }

    function deleteByGlobalManager(User $initiator): void {
        /* Так же я не вижу смысла возвращать пользователю ошибку, если пост isDeletedByGlobalManager, isDeletedByPageManager или isDeletedByCreator уже равно true,
         * хотя, возможно, в этом есть смысл, но это я узнаю в будущем.
         */
        if(!$initiator->isGlobalManager()) {
            throw new DomainException('No rights to softly delete post as global manager');
        }
        if(!$this->isDeletedByGlobalManager) {
            $this->deletedByGlobalManager = new \DateTime('now');
        }
        $this->isDeletedByGlobalManager = true;
    }
}
