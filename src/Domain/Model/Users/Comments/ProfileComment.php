<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Comments;

use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Users\Comments\Attachment;
use App\Domain\Model\Users\ProfileReaction;
use App\Domain\Model\Common\Reactable;

abstract class ProfileComment implements \App\Domain\Model\Common\Comments\Comment, Reactable {
    use \App\Domain\Model\EntityTrait;
    use \App\Domain\Model\Users\ProfileEntity;
    use \App\Domain\Model\Common\Comments\CommentTrait;
    
    protected bool $isDeleted = false;
    protected bool $isDeletedByGlobalManager = false;
    protected ?\DateTime $deletedAt = null;
    protected ?\DateTime $deletedByGlobalManagerAt = null;
    
    protected bool $isSoftlyDeleted = false;
    protected ?string $isSoftlyDeletedBy = null;
    
    protected ?Attachment $attachment;
    //protected ?Page $asPage;
    
    /** @var Collection<string, Reaction> $reactions */
    protected Collection $reactions;
    
    /** @var Collection<string, self> $replies */
    protected Collection $replies;
    
    /*
     Если свойство будет типа Comment, то так можно сделать двунаправленное отношение. Я не уверен нужно ли здесь двунаправленное отношение,
     возможно достаточно будет однонаправленного one-to-many, то есть чтобы была коллекция ответов, а здесь достаточно будет ?string, чтобы знать ID корневого коммента, что
     ведь это нужно для извлечения ответов отдельно от корневого коммента, например, через GET /comments/123/replies
     
     Это свойство может быть типа ProfileComment и здесь ничего плохого, всё равно наследники будут всё контроллировать и туда можно будет внедрять только объекты
     своего типа, то есть в UserPostComment::$root можно будет внедрить только объект класса UserPostComment
     */
    protected ?self $root; 
    /*
     * здесь string, потому что больше и не надо, мне так кажется сейчас.
     */
    protected ?self $replied;
    
    public function __construct(User $creator, string $text, User $owner, ?Attachment $attachment) {//, ?Page $asPage) {
        $this->id = (string)\Ulid\Ulid::generate(true);
        $this->creator = $creator;
        $this->text = $text;
        $this->owner = $owner;
       // $this->asPage = $asPage;
        
        if($attachment) {
            $this->setAttachment($attachment);
        }
    }
    
//    function onBehalfOfPage(): ?Page {
//        return $this->asPage;
//    }
    
    /** @return Collection<string, ProfileReaction> */
    function reactions(): Collection {
        /** @var ArrayCollection<string, ProfileReaction> $reactions */
        $reactions = $this->reactions;
        return $reactions;
    }
    
    /** @return Collection<string, ProfileComment> */
    function replies(): Collection {
        /** @var ArrayCollection<string, ProfileComment> $replies */
        $replies = $this->replies;
        return $replies;
    }
    
    function rootId(): ?string {
        $root = $this->root;
        return $root ? $root->id() : null;
    }
//    
//    function page(): ?Page {
//        return $this->asPage;
//    }
    
    function attachment(): ?Attachment {
        return $this->attachment;
    }
    
    function edit(string $text, ?Attachment $attachment): void {
        if((int)(new \DateTime('now'))->diff($this->createdAt)->format('%a') > 0) {
            throw new \App\Domain\Model\DomainExceptionAlt(
                [
                    'code' => \App\Application\Errors::EDIT_TIME_EXPIRED,
                    'message' => 'Edit time expired'
                ]
            );
        }
        $this->changeText($text);
        $this->attachment = $attachment;
    }
    
    function setAttachment(Attachment $attachment): void {
        $this->attachment = $attachment;
        $this->attachment->setComment($this);
    }
    
    abstract function repliesCount(): int;
    
//    /** @return Collection<string, self> */
//    abstract function replies(): Collection;
//    
//    /** @return Collection<string, ProfileReaction> */
//    abstract function reactions(): Collection;
    
    function isDeletedByGlobalManager(): bool {
        return $this->isDeletedByGlobalManager;
    }
    
    function isDeleted(): bool {
        return $this->isDeleted;
    }
    
    public function acceptReactableVisitor(\App\Domain\Model\Common\ReactableVisitor $visitor) {
        return $visitor->visitProfileComment($this);
    }

    
    /**
     * @template T
     * @param ProfileCommentVisitor <T> $visitor
     * @return T
     */
    abstract function acceptProfileCommentVisitor(ProfileCommentVisitor $visitor);
    
    function root(): ?self {
        return $this->root;
    }

    function repliedId(): ?string {
        return $this->repliedId;
    }
    
    function replied(): ?self {
        return $this->replied;
    }
    
    //abstract function react(User $reactor, string $type, ?Page $asPage): void;
    
//    function delete(User $initiator, bool $asGlobalManager): void {
//        if($asGlobalManager) {
//            if(!$initiator->isGlobalManager()) {
//                throw new DomainException("Cannot softly delete as global manager");
//            }
//            if($this->isDeletedByGlobalManager === false) {
//                $this->deletedByGlobalManagerAt = new \DateTime('now');
//            }
//            $this->isDeletedByGlobalManager = true;
//        }
//        else {
//            if(!$this->creator->equals($initiator) && !$this->owner->equals($initiator)) {
//                throw new DomainException("No rigths to softly delete");
//            }
//            if($this->isDeleted === false) {
//                $this->deletedAt = new \DateTime('now');
//            }
//            $this->isDeleted = true;
//        }
//    }

}
