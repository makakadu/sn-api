<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Videos\Comment;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Group\Group;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Groups\Videos\Video;
use App\Domain\Model\DomainException;
use App\Domain\Model\Groups\Comments\Attachment;
use App\Domain\Model\Groups\Comments\GroupComment;
use App\Domain\Model\Groups\GroupReaction;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Groups\Comments\Reaction as GroupCommentReaction;

class Comment extends GroupComment {
    use \App\Domain\Model\EntityTrait;
    use \App\Domain\Model\Groups\GroupEntity;
    
    private Video $commented;
    
    function __construct(
        Video $commented,
        User $creator,
        string $text,
        ?self $replied,
        bool $asGroup,
        ?Attachment $attachment
    ) {
        parent::__construct($creator, $commented->owningGroup(), $text, $attachment, $asGroup);
        if($replied) {
            if(!$commented->equals($replied->commented)) {
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
        $this->reactions = new ArrayCollection();
        $this->replies = new ArrayCollection();
    }
    
    function equals(self $comment): bool {
        return $this->id === $comment->id;
    }
    
    function commentedVideo(): Video {
        return $this->commented;
    }
    
    /** @return Collection<string, GroupReaction> */
    function reactions(): Collection {
        /** @var ArrayCollection<string, GroupReaction> $reactions */
        $reactions = $this->reactions;
        return $reactions;
    }
    
    /** @return Collection<string, GroupComment> */
    function replies(): Collection {
        /** @var ArrayCollection<string, GroupComment> $replies */
        $replies = $this->replies;
        return $replies;
    }

    function react(User $reactor, string $type, bool $asGroup): void {
        if($asGroup && !$this->owningGroup->isAdminOrEditor($reactor)) {
            throw new DomainException("No rights to react on behalf of group");
        }
        
        /** @var ArrayCollection<string, GroupReaction> $reactions */
        $reactions = $this->reactions;
        
        $criteria = Criteria::create();
        
        /* К сожалению это еще одна проблема, дело в том, что этот запрос может быть совершен одновременно в двух разных процессах и если в обеих возвратиться null, то будут созданы
         * 2 реакции от имени группы, хотя позволена только одна в коллекции. Это, вроде бы, можно решить с помощью concurrency, будет создано boolean свойство reactedByGroup,
         * но я не уверен, что это хорошая идея, возможно даже она не поможет, ведь оба процесса будут изменять Comment в одно и то же состояние (reactedByGroup === true) 
         * и возможно это не приведёт к ошибке, что плохо.
         * 
         * Но с помощью concurrency можно решить только проблему создания реакции от имени группы, а если реакция создаётся от имени страницы(не здесь), то
         * свойство похожее на reactedByGroup никак не поможет, ведь страниц множество. Проблема в том, что создать реакцию от имени одной и той же страницы могут разные пользователи и 
         * из-за этого невозможно сделать уникальный индекс, то есть это нельзя контроллировать на уровне базы данных. Могут быть разные комбинации свойств Comment - User - Page, 
         * то есть, допустим есть 2 реакции:
         * 1. CommentId - 123, PageId - 228, User - 666
         * 2. CommentId - 123, PageId - 228, User - 333
         * Они могут находиться в одной таблице и никаких ошибок не будет при сохранении второй.
         * Нельзя сделать уникальный индекс без User, потому что тогда будет ещё хуже. 
         * Да и нельзя добавить PageId в индекс, потому что вместо Page может быть null.
         * 
         * Пользователь может создать реакцию от имени разных страниц, а это значит, что даже уникальный индекс из CommentId и UserId нельзя создать. То есть нужен также ID страницы.
         * Но если реакция создана не от имени страницы, то что делать? Сделать ID страницы nullable или пустой строкой в случае отсутствия?
         * 
         * Возможно можно создать 2 уникальных индекса, в первом только CommentId и PageId, а во втором CommentId, UserId и PageId. Первый нужен того, чтобы была только одна реакция
         * от имени конкретной страницы на конкретный комментарий. А второй для того, чтобы пользователь мог создать больше одной реакции, если PageId - это пустая строка или nullable, то
         * реакция создана от имени пользователя, если нет, то от имени страницы.
         */
        if($asGroup) {
            $criteria->where(Criteria::expr()->eq("asGroup", true));            
            if($reactions->matching($criteria)->count()) {
                throw new DomainException("Already reacted on behalf of group");
            }
        } else {
            $criteria->where(Criteria::expr()->eq("user", $reactor));
            if($reactions->matching($criteria)->count()) {
                throw new DomainException("User {$reactor->id()} already reacted ");
            }
        }
        $reaction = new GroupCommentReaction($reactor, $this, $type, $asGroup);
        $this->reactions->add($reaction);
    }
    
    function isRoot(): bool {
        return (bool)!$this->root;
    }

    public function repliesCount(): int {
        return 0;
    }
    
    function repliedId(): ?string {
        return $this->repliedId;
    }
    
    public function acceptGroupCommentVisitor(\App\Domain\Model\Groups\Comments\GroupCommentVisitor $visitor) {
        return $visitor->visitVideoComment($this);
    }

}
