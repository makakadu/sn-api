<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Photos;

use App\Domain\Model\Common\DescribableTrait;
use App\Domain\Model\Common\PhotoTrait;
use App\Domain\Model\DomainException;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Groups\Group\Group;
use App\Domain\Model\Groups\Photos\AlbumPhoto\Comment\Comment;
use App\Domain\Model\Users\User\User;
use Assert\Assertion;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Groups\Comments\Attachment as CommentAttachment;
use App\Domain\Model\Groups\GroupReaction;
use App\Domain\Model\Common\Reactable;

abstract class Photo implements \App\Domain\Model\Common\PhotoInterface, \App\Domain\Model\Common\Shares\Shareable, Reactable {
    const DESCRIPTION_MAX_LENGTH = 300;
    
    use EntityTrait;
    use \App\Domain\Model\Groups\GroupEntity;
    use PhotoTrait;
    use DescribableTrait;
    
    protected User $creator;
    
    protected bool $isDeletedByMember = false;
    protected bool $isDeletedByLocalManager = false;
    protected bool $isDeletedByGlobalManager = false;
    
    protected ?\DateTime $deletedAt = null;
//    protected ?\DateTime $deletedAtByMember = null;
//    protected ?\DateTime $deletedAtByLocalManager = null;
//    protected ?\DateTime $deletedAtByGlobalManager = null;
    
    /** @var Collection<string, GroupReaction> $reactions */
    protected Collection $reactions;
    
    /**
     * @param array<string> $versions
     */
    function __construct(Group $owningGroup, User $creator, array $versions) {
        $this->id = (string) \Ulid\Ulid::generate(true);
        $this->owningGroup = $owningGroup;
        $this->creator = $creator;
        $this->setVersions($versions);
        
        $this->reactions = new ArrayCollection();
        
        $this->createdAt = new \DateTime("now");
    }
    
    public function creator(): User {
        return $this->creator;
    }
    
    /** @return Collection<string, GroupReaction> */
    function reactions(): Collection {
        return $this->reactions;
    }
    
    function equals(self $photo): bool {
        return $this->id === $photo->id;
    }
    
    function isSoftlyDeleted(): bool {
        return false;
    }
    
    public function changeDescription(string $description): void {
        Assertion::length($description, self::DESCRIPTION_MAX_LENGTH, sprintf("Description of photo should be less than %s", self::DESCRIPTION_MAX_LENGTH));
        $this->description = $description;
    }

    abstract function comment(User $creator, string $text, ?string $repliedId, bool $asGroup, ?CommentAttachment $attachment): void;

//    function react(User $user, string $type, bool $asGroup): void {
//        $reaction = new Reaction($user, $this, $type, $asGroup);
//        $this->reactions->add($reaction);
//    }
    /*
    // Как-то странно выглядит, что здесь связывается Photo из ProfilePicture, ведь по названию кажется, что ProfilePicture - это уже что-то типа фото, а самом деле нет, без Photo это вообще ничто
    // Мне кажется, что это лучший способ из всех, это, наверное, максимальный компромисс между правильным кодом и удобством. 
    
    // Это установление между Photo и ProfilePicture, чтобы
    // 1. Было понятно, что это фото является картинкой профиля
    // 2. Чтобы при удалении Photo удалялся связанный ProfilePicture
    
    // Здесь ситуация с исключениями хуже, чем ниже, дело в том, что при создании ProfilePicture всегда создаётся новое фото и просто не может быть такого, что фото уже связано с чем-либо, оно
    // будет новым. Поэтому все эти проверки нужны только для перестраховки и в случае, если какое-то из этих исключений будет выброшено, то значит я где-то ошибся.
    // Мне это не нравится, потому что это похоже на бизнес логику. Это даже сложно назвать логической ошибкой, потому что ошибка может быть из-за внутреннего состояния ProfilePicture и
    // внутреннего состояния Photo. Это самая настоящая доменная логика. Если выбрасывется DomainException, то для меня это сигнал о том, что всё ок. Если здесь будет выброшено DomainException,
    // то это не ок.
    */

    /**
     * @return mixed
     */
    public function accept(\App\Domain\Model\Common\PhotoVisitor $visitor) {
        
    }

}
