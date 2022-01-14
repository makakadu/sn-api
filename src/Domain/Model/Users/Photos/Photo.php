<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Photos;

use App\Domain\Model\Common\DescribableTrait;
use App\Domain\Model\Common\PhotoInterface;
use App\Domain\Model\Common\PhotoTrait;
use App\Domain\Model\Common\PhotoVisitor;
use App\Domain\Model\Common\PhotoVisitorVisitable;
use App\Domain\Model\Common\Shares\Shareable;
use App\Domain\Model\DomainException;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\Albums\Album;
use App\Domain\Model\Users\Photos\Comment\Comment;
use App\Domain\Model\Users\User\User;
use Assert\Assertion;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Users\Comments\Attachment as CommentAttachment;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Users\Photos\Reaction;
use App\Domain\Model\Common\Reactable;
use App\Domain\Model\Saveable;
use App\Domain\Model\Users\ProfileReaction;

abstract class Photo implements PhotoInterface, PhotoVisitorVisitable {
    const DESCRIPTION_MAX_LENGTH = 300;
    
    use EntityTrait;
    use PhotoTrait;
    use DescribableTrait;

    protected User $user;
    
    protected bool $isDeleted = false;
    protected bool $isDeletedByGlobalManager = false;
    protected ?\DateTime $deletedAt = null;
    protected ?\DateTime $deletedByGlobalManagerAt = null;
    
    /** @var Collection<string, Reaction> $reactions */
    protected Collection $reactions;

    /** @param array<string> $versions */
    function __construct(User $creator, array $versions) {
        $this->id = (string) \Ulid\Ulid::generate(true);
        $this->user = $creator;
        $this->setVersions($versions);
        $this->createdAt = new \DateTime("now");
        
        $this->reactions = new ArrayCollection();
    }
    
    function equals(self $photo): bool {
        return $this->id === $photo->id;
    }
    
    /** @return Collection<string, ProfileReaction> */
    function reactions(): Collection {
        /** @var Collection<string, ProfileReaction> $reactions */
        $reactions = $this->reactions;
        return $reactions;
    }

    function delete(bool $byManager = false): void {
//        if($byManager) {
//            if($this->isDeleted) {
//                throw new DomainException("Photo cannot be softly deleted by manager if is already softly deleted by user");
//            }
//            $this->isDeletedByManager = true;
//            $this->deletedAt = new \DateTime('now');
//        } else {
//            if($this->isDeletedByManager) {
//                throw new DomainException("Photo cannot be softly deleted by user if is already softly deleted by manager");
//            }
//            $this->isDeleted = true;
//            $this->deletedAt = new \DateTime('now');
//        }
    }
    
    function restore(bool $byManager = false): void {
//        if($byManager) {
//            // Если isDeletedByManager === true, то это значит, что isDeleted === false, поэтому здесь не нужно проверять isDeleted.
//            // Если false, то ничего не будет изменено, поэтому всё ок
//            $this->isDeletedByManager = false; // Если isDeletedByManager уже был false, то ничего страшного, что ничего не меняется, главное, что инварианты соблюдены
//            $this->deletedAt = null;
//        } else {
//            $this->isDeleted = false;
//            $this->deletedAt = null;
//        }
    }
    
    function isDeleted(): bool {
        return $this->isDeleted;
    }
    
    function isDeletedByGlobalManager(): bool {
        return $this->isDeletedByGlobalManager;
    }
    
    function owner(): User {
        return $this->user;
    }
    
    function isSoftlyDeleted(): bool {
        return $this->isDeleted || $this->isDeletedByGlobalManager;
    }

    public function changeDescription(string $description): void {
        if($this->isSoftlyDeleted()) {
            throw new DomainException("Cannot add/change description to softly deleted photo");
        }
        Assertion::maxLength($description, self::DESCRIPTION_MAX_LENGTH, sprintf("Description of photo should be less than %s", self::DESCRIPTION_MAX_LENGTH));
        $this->description = $description;
    }

//    // Комментирование альбомного фото и картинки профиля почти одинаковое, только для комментирования первого нужно провести авторизацию альбома. Если эта авторизация
//    // внешняя, что более предпочтительно из-за некоторых ньюансов, то можно делать этом метод НЕ абстрактным и он подойдёт для обеих типов фото. 
//    function comment(User $creator, string $text, ?string $repliedId, ?CommentAttachment $attachment, ?Page $asPage): void {
//        if($this->isDeleted || $this->isDeletedByGlobalManager) {
//            throw new DomainException("Cannot comment because it is softly deleted");
////        } elseif($this->offComments) {
////            throw new DomainException("Comments are disabled");
//        } elseif ($this->comments->count() >= 4000) {
//            throw new DomainException("Max number(4000) of comments has been reached");
////        } elseif(!$this->isPublic && !$this->creator->equals($creator) && !$this->isConnectedWith($creator)) {
////            throw new DomainException("No rights to comment");
//        } elseif($asPage && !$asPage->isAdminOrEditor($creator)) {
//            throw new DomainException("No rights to comment on behalf of given page");
//        } elseif($asPage && !$asPage->isAllowedForExternalActivity()) { /* Если страница не набрала достаточное число подписчиков, плохо оформлена и так далее, то внешняя активность запрещена.
//             * Это защита от страниц, которые созданы для сомнительных действий
//             * Возможно стоит перенести в сервис авторизации по нескольким причинам:
//             * 1. Возможно нужно будет использовать репозитории
//             * 2. Код будет повторяться во многих местах
//             */
//            throw new DomainException("Cannot comment on behalf of given page because commenting in profiles is not allowed for this page");
//        }
//        $replied = null;
//        if($repliedId) {
//            $replied = $this->comments->get($repliedId);
//            if(!$replied) {
//                throw new DomainException("Cannot replied to comment $repliedId, no such comment found");
//            }
//        }
//        $comment = new Comment($this, $creator, $text, $replied, $attachment, $asPage);
//        $this->comments->add($comment);
//    }

//    // Здесь то же самое, что и с комментированием
//    function react(User $reactor, string $type, ?Page $asPage): void {
//        if($asPage && !$asPage->isAllowedForExternalActivity()) {
//            throw new DomainException("Cannot react on behalf of given page because reacting in profiles is not allowed for this page");
//        }
//        if($asPage && !$asPage->isAdminOrEditor($reactor)) {
//            throw new DomainException("No rights to react on behalf of given page");
//        }
//        
//        /** @var ArrayCollection<string, Reaction> $reactions */
//        $reactions = $this->reactions;
//        
//        $criteria = Criteria::create();
//        
//        if($asPage) {
//            $criteria->where(Criteria::expr()->eq("asPage", $asPage));
//            if($reactions->matching($criteria)->count()) {
//                throw new DomainException("Already reacted on behalf of page {$asPage->id()}");
//            }
//        } else {
//            $criteria->where(Criteria::expr()->eq("creator", $reactor));
//            if($reactions->matching($criteria)->count()) {
//                throw new DomainException("User {$reactor->id()} already reacted");
//            }
//        }
//
//        $reaction = new Reaction($reactor, $this, $type, $asPage);
//        $this->reactions->add($reaction);
//    }

    public function accept(PhotoVisitor $visitor) {
        
    }

}
