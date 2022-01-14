<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Group;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Common\Shares\Shared;
use App\Domain\Model\Groups\Post\Post;
use App\Domain\Model\Groups\PhotoAlbum\PhotoAlbum;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\DomainException;
use App\Domain\Model\Groups\Videos\Video;
use App\Domain\Model\Groups\Photos\GroupPicture\GroupPicture;
use App\Application\Errors;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Groups\Membership\Membership;
use App\Domain\Model\Groups\Ban\Ban;
/*
 * Чтобы создавать что-то в группе, нужно быть участником. Если группа закрыта, то видеть что там у неё внутри могут только участники.
 * Если группа открыта, то видеть контент группы могут все, но создавать контент могут только участники
 * Без подтверждения или без приглашения пользователь не может вступить в группу
 * 
 * Если группа скрыта, то значит группа не может быть найдена через поиск, незареганные не могут посмотреть контент
 * 
 * Участие и менеджмент в группе устроены по-другому, чем на странице. Дело в том, что Subscription и Membership - это разные вещи, поэтому мне кажется, что менеджер обязательно
 * должен быть участником и возможно стоит объединить Membership и Manager.
 * В вк, если пользователь является менеджером группы, не теряет свою должность, если выходит из группы, к тому же, если группа закрытая, то он может вступить обратно без
 * подтверждения.
 */

class Group {
    use \App\Domain\Model\EntityTrait;
    
    private User $owner;
    private User $creator;
    private string $name;
    //private string $description;
    
    /** @var Collection<string, PhotoAlbum> $photoAlbums */
    private Collection $photoAlbums;
    /** @var Collection<string, Post> $posts*/
    private Collection $posts;
    /** @var Collection<string, Video> $videos*/
    private Collection $videos;
    /** @var Collection<string, GroupPicture> $pictures*/
    private Collection $pictures;
    /** @var Collection<string, Membership> $memberships */
    private Collection $memberships;
    /** @var Collection<string, Manager> $managers */
    private Collection $managers;
    /** @var Collection<string, Ban> $bans */
    private Collection $bans;
    /* Нельзя сделать из открытой группы приватную. Зато можно сделать из приватной открытую 
     Если группа приватная, то никто, кроме участников и менеджеров, не может видеть контент группы и создавать контент
     Если группа НЕ приватная, то кто угодно может видеть контент группы, но создавать контент могут только участники и менеджеры группы
    */
    
    private Settings $settings;
    private SectionsSettings $sectionsSettings;
    
    //private bool $canMembersInviteConnections = true; // Мне кажется это неуместным

    function __construct(User $creator, string $name, bool $private, bool $visible) {
        $this->name = $name;
        $this->creator = $creator;
        $this->owner = $creator;
        
        $this->id = (string)\Ulid\Ulid::generate(true);
        $this->createdAt = new \DateTime("now");
        // $this->canMembersInviteConnections = $canMembersInviteConnections;
        //$this->isPrivate = $isPrivate;
        
        $this->photoAlbums = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->pictures = new ArrayCollection();
        $this->settings = new Settings($this, $private, $visible);
        $this->sectionsSettings = new SectionsSettings($this);
    }
    
    function owner(): User {
        return $this->owner;
    }
    
    /** @param array<mixed> $attachments */
    function createPost(
        User $creator,
        string $text,
        bool $disableComments, 
        ?Shared $shared, 
        array $attachments, 
        bool $onBehalfOfGroup,
        bool $addSignature
    ): Post {
        
        if(!$this->isMemberOrManager($creator)) { // Если пользователь не является участником или менеджером, то он точно не может создать пост
            throw new DomainException("Only members and managers can create post");
        }
        if($onBehalfOfGroup && !$this->isAdminOrEditor($creator)) {
            throw new DomainException("Only managers can create post on behalf of group");
        }
        if($disableComments && !$onBehalfOfGroup) {
            throw new DomainException("Comments cannot be disabled if post created on behalf of member");
        }
        if($addSignature && !$this->isAdminOrEditor($creator)) {
            // Можно проигнорить это
            throw new DomainException("Signature cannot be added to post created on behalf of group");
        }
        
        $wallSection = $this->settings->wallSection();
        if(($wallSection === Settings::RESTRICTED || $wallSection === Settings::CLOSED) && !$onBehalfOfGroup) {
            throw new DomainException("If wall is restricted or closed post can be created only of behalf of group");
        }
        if($wallSection === Settings::DISABLED) {
            throw new DomainException("Cannot create post on closed wall");
        }
        $post = new Post($this, $creator, $text, $shared, $disableComments, $attachments, $onBehalfOfGroup, $addSignature);
        $this->posts->add($post);
        
        return $post;
    }
    
    function createPhotoAlbum(User $creator, string $name, string $description, bool $offComments, bool $isRestricted): PhotoAlbum { // Альбом могу создать только менеджеры
        if($this->photoAlbums->count() === 10000) {
            throw new DomainException("Max amount of albums reached");
        }
        $photosSection = $this->settings->photosSection();
        if($photosSection === 1 && !$this->isAdminOrEditor($creator)) { // Если альбомы могут создавать только редакторы и админы
            throw new DomainException("No rights to create photo album");
        }
        $album = new PhotoAlbum($creator, $this, $name, $description, $offComments, $isRestricted);
        $this->photoAlbums->add($album);
        
        return $album;
    }
    
    function createMembership(User $initiator, User $target): void {
        if(!$this->isAdmin($initiator) && !$this->owner->equals($initiator)) {
            throw new DomainException("Only admins and owner can invite users to group");
        }
        if($this->isMember($target)) {
            throw new DomainException("User {$target->id()} is a member already");
        }
        if($this->isInvited($target)) {
            throw new DomainException("User {$target->id()} is invited already");
        }
        if($this->userIsBanned($target)) {
            throw new DomainException("Banned user cannot be invited to group");
        }
        $membership = new Membership($this, $initiator, $target);
        $this->memberships->add($membership);
    }
    
    function deleteMembership(User $initiator, string $membershipId): void {
        $membership = $this->memberships->get($membershipId);
        if(!$membership) {
            throw new \App\Application\Exceptions\NotExistException("Membership $membershipId not found");
        }
        if($membership->memberId() !== $initiator->id()) {
            if(!$this->isAdmin($initiator) && !$this->owner->equals($initiator)) {
                throw new DomainException("No rigths remove member from group (no rights to delete membership)");
            }
        }
        $this->memberships->removeElement($membership);
    }
    
    function addManager(User $initiator, User $user, string $position, bool $showInContacts): void {
        if(!$this->isAdmin($initiator) && !$this->owner->equals($initiator)) {
            throw new DomainException("No rights to add manager");
        }
        if(!$this->isMember($user)) {
            throw new DomainException("Not a member cannot be manager of group");
        }
        if($this->isManager($user)) {
            throw new DomainException("User {$user->id()} is a manager already");
        }
        
        $criteria3 = Criteria::create()
            ->where(Criteria::expr()->eq("banned", $user))
            ->setMaxResults(1);
        $matched = $this->bans->matching($criteria3);
        if(count($matched)) {
            $this->bans->removeElement($matched[0]);
        }
        $manager = new Manager($this, $user, $position, $showInContacts);
        $this->managers->add($manager);
    }
    
    function userIsBanned(User $user): bool {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("banned", $user))
            ->setMaxResults(1);
        
        return (bool)count($this->bans->matching($criteria));
    }
    
    /*
     * Если бан перманентный, то пользователья выгоняют из группы
     */
    function banUser(User $initiator, User $target, ?int $minutes, string $reason, ?string $message): void {
        
        if(!$this->isManager($initiator)) {
            throw new DomainException("No rights to ban user");
        }
        if($this->isManager($target)) {
            throw new DomainException("Cannot ban manager");
        }
        
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("banned", $target))
            ->andWhere(Criteria::expr()->eq("deletedAt", ""));
        
        $matched = $this->bans->matching($criteria);

        if(count($matched)) {
            
            $end = $matched[0]->getEnd();

            $matched = $matched[0];
            if(!$end || ($end && $end > new \DateTime('now'))) {
                throw new \App\Application\Exceptions\UnprocessableRequestException(123, "User ".$target->id()." is already banned");
            }
            if($end && $end < new \DateTime('now')) {
                $matched->delete(); /* Если найденный бан закончился, то помечаем его удалённым, чтобы можно было создать новый бан, если не пометить, то будет ошибка из-за unique constraint */
            }
        }

        $ban = new Ban($this, $initiator, $target, $minutes, $reason, $message);
        $this->bans->add($ban);
        
        if(!$minutes) {
            $criteria = Criteria::create()->where(Criteria::expr()->eq("memberId", $target->id()));

            $memberships = $this->memberships->matching($criteria);
            if(count($memberships)) {
                $this->memberships->removeElement($memberships[0]);
            }
        }

    }
    
    function autoApprovalsAreEnabled(): bool {
        return $this->settings->autoApprovalsAreEnabled();
    }
    
    function wallSection(): int {
        return $this->settings->wallSection();
    }
    
    function settings(): Settings {
        return $this->settings;
    }
    
    function isDeleted(): bool {
        return false;
    }
    
    function equals(self $group): bool {
        return $this->id === $group->id;
    }
    
    function isEditor(User $user): bool {
        $manager = $this->getManager($user);
        return $manager ? $manager->position() === 'editor' : false;
    }
    
    function isAdmin(User $user): bool {
        $manager = $this->getManager($user);
        return $manager ? $manager->position() === 'admin' : false;
    }
    
    function isModer(User $user): bool {
        $manager = $this->getManager($user);
        return $manager ? $manager->position() === 'moder' : false;
    }
    
    function isAdminOrEditor(User $user): bool {
        return $this->owner->equals($user) || $this->isAdmin($user) || $this->isEditor($user);
    }
    
    function isMember(User $user): bool {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("memberId", $user->id()))
            ->andWhere(Criteria::expr()->eq("accepted", 1));
        
        /** @var ArrayCollection<string, Membership> $memberships */
        $memberships = $this->memberships;
        
        /** @var ArrayCollection <string, Membership> $match */
        return (bool)count($memberships->matching($criteria));
    }
    
    function isInvited(User $user): bool {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("memberId", $user->id()))
            ->andWhere(Criteria::expr()->eq("accepted", 0));
        
        /** @var ArrayCollection<string, Membership> $memberships */
        $memberships = $this->memberships;
        
        /** @var ArrayCollection <string, Membership> $match */
        return (bool)count($memberships->matching($criteria));
    }
    
    function isManager(User $user): bool {
        return !is_null($this->getManager($user)) || $this->owner->equals($user);
    }
    
    function isMemberOrManager(User $user): bool { // Возвращает true, если пользователь имеет доступ к группе. Я еще не решил будет ли возвращаться true, если группа открыта
        // но пользователь не является участником или менеджером группы.
        return $this->isMember($user) || $this->isManager($user);
    }
    
    private function getManager(User $user): ?Manager {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("manager", $user));
        
        /** @var ArrayCollection<string, Manager> $managers */
        $managers = $this->managers;
        
        /** @var ArrayCollection <string, Manager> $match */
        $match = $managers->matching($criteria);
        
        if($match->count()) {
            $manager = $match->first(); // Почему-то метод first() может возвратить false, поэтому если возвращается false - возвращаем null
            return $manager ? $manager : null;
        } else {
            return null;
        }
    }
    
    function isPrivate(): bool {
        return $this->settings->isPrivate();
    }

//    function ban(User $user, int $reason, int $term, string $comment): Ban {
////        if(!$this->isMember($userId)) {
////            
////        } else if($this->inBannedList($userId)) {
////            
////        } else if($this->isManager($userId)) {
////            
////        }
//        
//        $ban = new Ban($this->id, $userId, $comment, $term, $reason);
//    }

    function currentPicture(): ?GroupPicture {
        $criteria = Criteria::create()
            ->orderBy(array('updatedAt' => Criteria::DESC))
            ->setMaxResults(1);
        /** @var ArrayCollection<string, GroupPicture> $groupPictures */
        $groupPictures = $this->pictures;
        
        $matched = $groupPictures->matching($criteria);
        if(count($matched)) {
            $key = array_key_first($matched->toArray());
            return $matched[$key];
        } else {
            return null;
        }
    }
    
    public function name(): string {
        return $this->name;
    }

}
