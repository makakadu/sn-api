<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Photos;

use App\Domain\Model\Common\DescribableTrait;
use App\Domain\Model\Common\PhotoInterface;
use App\Domain\Model\Common\PhotoTrait;
use App\Domain\Model\Common\PhotoVisitor;
use App\Domain\Model\Common\PhotoVisitorVisitable;
use App\Domain\Model\Common\Shares\Shareable;
use App\Domain\Model\DomainException;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;
use Assert\Assertion;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Pages\Comments\Attachment as CommentAttachment;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Pages\Photos\Comments\Comment;
use App\Domain\Model\Pages\PageReaction;
use App\Domain\Model\Common\Reactable;

abstract class Photo implements PhotoInterface, PhotoVisitorVisitable, Shareable, Reactable {
    const DESCRIPTION_MAX_LENGTH = 300;
    
    use EntityTrait;
    use \App\Domain\Model\Pages\PageEntity;
    use PhotoTrait;
    use DescribableTrait;

    // Возможно свойство creator нужно только в AlbumPhoto, а в PagePicture в нём нет смысла, хотя, возможно, и понадобится
    
    protected User $creator;
    
    /** @var Collection<string, Reaction> $reactions */
    protected Collection $reactions;
    
    protected bool $isDeleted = false;
    protected bool $isDeletedByLocalManager = false;
    protected bool $isDeletedByGlobalManager = false;
    protected ?\DateTime $deletedAt = null;
            
    /** @param array<string> $versions */
    function __construct(User $creator, Page $owningPage, array $versions) {
        $this->id = (string) \Ulid\Ulid::generate(true);
        $this->creator = $creator;
        $this->owningPage = $owningPage;
        $this->setVersions($versions);
        $this->createdAt = new \DateTime("now");
    }
    
    /** @return Collection<string, PageReaction> */
    function reactions(): Collection {
        /** @var Collection<string, PageReaction> $reactions */
        $reactions = $this->reactions;
        return $reactions;
    }
    
    public function creator(): User {
        return $this->creator;
    }
//        
//    function delete(bool $asLocalManager, bool $asGlobalManager): void {
//        if($asLocalManager && $asGlobalManager) {
//            throw new \LogicException("Only one of \$asManager and \$asGlobalManager parameters can be true");
//        }
//        
//        if($asLocalManager) {
//            if($this->isDeletedByGlobalManager && !$this->isDeletedByLocalManager) { /* Если фото удалено глобальным менеджером, то стоит выбросить исключение, ведь, как мне кажется, нет смысла разрешать мягко удалить этот пост
//                менеджерам страницы, если он уже мягко удалён глобальным менеджером
//                Но если $isDeletedByLocalManager === true, то нет смысла выбрасывать исключение, ведь ничего не изменится
//             */
//                throw new DomainException("Photo cannot be softly deleted by manager if is already softly deleted by user");
//            }
//            if(!$this->isDeletedByLocalManager) { // Изменить дату нужно только в случае, если до этого $this->isDeletedByLocalManager было false, ведь если $this->isDeletedByLocalManager
//                $this->deletedAt = new \DateTime('now'); // true, то $this->deletedAtByLocalManager НЕ null, то есть дата уже установлена, если установить дату ещё раз,
//                // то будет стерта реальная дата удаления
//            }
//            $this->isDeletedByLocalManager = true;
//        }
//        elseif($asGlobalManager) {
//            if(!$this->isDeletedByGlobalManager) {
//                $this->deletedAt = new \DateTime('now');
//            }
//            $this->isDeletedByGlobalManager = true;
//        }
//        else {
//            if($this->isDeletedByLocalManager && !$this->isDeleted) {
//                throw new DomainException("Photo cannot be softly deleted by creator if is already softly deleted by local manager");
//            } 
//            if($this->isDeletedByGlobalManager && !$this->isDeleted) {
//                throw new DomainException("Photo cannot be softly deleted by creator if is already softly deleted by global manager");
//            } 
//            if(!$this->isDeleted) {
//                $this->deletedAt = new \DateTime('now');
//            }
//            $this->isDeleted = true;
//        }
//    }
//    
//    function restore(bool $asLocalManager, bool $asGlobalManager): void {
//        if($asLocalManager && $asGlobalManager) {
//            throw new \LogicException("Only one of \$asManager and \$asGlobalManager parameters can be true");
//        }
//        
//        // Если, например, фото удалено глобальным менеджером, то 
//        if($asLocalManager) {
//            $this->isDeletedByLocalManager = false;
//            $this->deletedAt = null;
//        } elseif($asGlobalManager) {
//            $this->isDeletedByGlobalManager = false;
//            $this->deletedAt = null;
//        } else {
//            $this->isDeleted = false;
//            $this->deletedAt = null;
//        }
//    }
//    
    function isDeleted(): bool {
        return $this->isDeleted;
    }
    
    function isDeletedByLocalManager(): bool {
        return $this->isDeletedByLocalManager;
    }
    
    function isDeletedByGlobalManager(): bool {
        return $this->isDeletedByGlobalManager;
    }
    
    function isSoftlyDeleted(): bool {
        return $this->isDeleted || $this->isDeletedByLocalManager || $this->isDeletedByGlobalManager;
    }

//    public function changeDescription(string $description): void {
//        if($this->isSoftlyDeleted()) {
//            throw new DomainException("Cannot add/change description to softly deleted photo");
//        }
//        Assertion::maxLength($description, self::DESCRIPTION_MAX_LENGTH, sprintf("Description of photo should be less than %s", self::DESCRIPTION_MAX_LENGTH));
//        $this->description = $description;
//    }

//    public function comment(User $creator, string $text, ?string $repliedId, ?CommentAttachment $attachment, ?Page $asPage): void {
//        if($this->isSoftlyDeleted()) {
//            throw new DomainException("Cannot comment softly deleted photo");
//        }
//
//        $replied = null;
//        if($repliedId) {
//            $replied = $this->comments->get($repliedId);
//            if(!$replied) {
//                throw new DomainException("Cannot replied to comment $repliedId, no such comment found");
//            }
//        }
//        $comment = new Comment($this, $creator, $text, $replied, $asPage, $attachment);
//        $this->comments->add($comment);
//    }
//
//    function react(User $user, string $type, ?Page $asPage): void {
//        if($this->isSoftlyDeleted()) {
//            throw new DomainException("Cannot react to softly deleted photo");
//        }
//        
//        /** @var ArrayCollection<string, Reaction> $reactions */
//        $reactions = $this->reactions;
//        
//        $criteria = Criteria::create()
//            ->where(Criteria::expr()->eq("creator", $user));
//        if($reactions->matching($criteria)->count()) {
//            throw new DomainException("User {$user->id()} already reacted to this photo");
//        }
//        $reaction = new Reaction($user, $this, $type, $asPage);
//        $this->reactions->add($reaction);
//    }
    
    function equals(self $photo): bool {
        return $this->id === $photo->id;
    }

    public function accept(PhotoVisitor $visitor) {
        
    }

}
