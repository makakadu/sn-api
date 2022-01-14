<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Page;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Photos\PagePicture\PagePicture;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Pages\PhotoAlbum\PhotoAlbum;
use App\Domain\Model\Pages\Photos\AlbumPhoto;
use App\Domain\Model\Pages\Videos\Video;
use App\Domain\Model\Pages\Post\Post;
use App\Domain\Model\Pages\Ban\Ban;
use App\Domain\Model\Pages\VideoPlaylist\VideoPlaylist;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Common\Shares\Shared;
use App\Domain\Model\Pages\SuggestedPost\SuggestedPost;
use App\Domain\Model\DomainException;

class Page {
    use \App\Domain\Model\EntityTrait;
    
    private User $creator; // Создатель никогда не меняется, это свойство нужно для истории
    private User $owner; // Владелец может быть изменён
    private string $name;
    private string $description;
    private string $subject;
    
    private bool $acceptOfferedPostsFromAll;
    
    /** @var Collection<string, VideoPlaylist> $videoPlaylists */
    private Collection $videoPlaylists;
    /** @var Collection<string, PhotoAlbum> $photoAlbums */
    private Collection $photoAlbums;
    /** @var Collection<string, Post> $posts */
    private Collection $posts;
    /** @var Collection<string, SuggestedPost> $suggestedPosts */
    private Collection $suggestedPosts;
    /** @var Collection<string, PagePicture> $pictures */
    private Collection $pictures;
    /** @var Collection<string, Video> $videos */
    private Collection $videos;
    /** @var Collection<string, Ban> $bans */
    private Collection $bans;
    /** @var Collection<string, Manager> $managers */
    private Collection $managers;
    
    private Sections $sections;
    
    function __construct(
        User $creator,
        string $name,
        string $description,
        string $subject
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->subject = $subject;
        
        $this->id = (string) \Ulid\Ulid::generate(true);
        
        $this->videos = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->suggestedPosts = new ArrayCollection();
        $this->pictures = new ArrayCollection();  
        $this->photoAlbums = new ArrayCollection();
        $this->bans = new ArrayCollection();
        $this->managers = new ArrayCollection();
        $this->creator = $creator;
        $this->owner = $creator;
        
        $this->sections = new Sections();
        
       // $this->settings = new Settings($language, $this);
        
        //$this->isActivated = true;
        $this->createdAt = new \DateTime('now');
    }
    

    
    function isBanned(User $user): bool {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("banned", $user))
            ->andWhere(Criteria::expr()->gt("end", new \DateTime('now')))
            ->andWhere(Criteria::expr()->eq("uniqueValue", "active"));
        
        $matched = $this->bans->matching($criteria);
        return (bool)count($matched);
    }
    
    function isSubscriber(User $user): bool {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("user", $user))
            ->andWhere(Criteria::expr()->eq("isDisabled", false));
        
        $matched = $this->subscriptions->matching($criteria);
        return (bool)count($matched);
    }
    
    function createAlbum(User $creator, string $name, string $description, bool $protected, bool $disableComments): void {
        if($this->photoAlbums->count() >= 1000) {
            throw new DomainException("Max amount of albums reached");
        }
        if(!$this->isAdminOrEditor($creator)) {
            throw new DomainException("No rights to create album");
        }
        if(!$this->sections->photosSection()) {
            throw new DomainException("Section with photos is disabled");
        }
        $album = new PhotoAlbum($creator, $this, $name, $description, $disableComments, $protected);
        $this->photoAlbums->add($album);
    }
    
    /**
     * @param array<mixed> $attachments
     */
    function createSuggestedPost(User $creator, string $text, array $attachments, bool $addSignature, bool $hideSignatureIfEdited): void {
        if($this->isBanned($creator)) { // Предлагать могут все, кроме забаненных. Возможно стоит запрещать пользователю предлагать посты, но не банить его. Но опять же это
            throw new DomainException("Banned users cannot suggest posts"); // может привет
        }
        $post = new \App\Domain\Model\Pages\Post\SuggestedPost\SuggestedPost($this, $creator, $text, $attachments, $addSignature, $hideSignatureIfEdited);
        $this->suggestedPosts->add($post);
    }
    
    /* Поскольку создание поста с нуля и из предложенного поста происходит через один апп сервис, нужно передать все параметры, включая $addSignature, который нафиг не нужен,
     * когда пост создаётся на основе предложенного поста, потому что тогда $addSignature формируется на основе данных из предложенного поста.
     */
    /**
     * @param array<mixed> $attachments
     */
    function createPost(
        User $creator,
        string $text,
        bool $disableComments,
        bool $disableReactions,
        ?Shared $shared,
        array $attachments,
        bool $addSignature
    ): void {
        if(!$this->isAdminOrEditor($creator)) {
            throw new DomainException("No rights to create post");
        }
        $post = new Post($this, $creator, $creator, $text, $shared, $disableComments, $disableReactions, $attachments, $addSignature);
        $this->posts->add($post);
    }
    
    /**
     * @param array<mixed> $attachments
     */
    function createPostFromSuggested(
        \App\Domain\Model\Pages\Post\SuggestedPost\SuggestedPost $suggested,
        User $publisher,
        string $text,
        bool $disableComments,
        bool $disableReactions,
        array $attachments
    ): void {
        if(!$this->isAdminOrEditor($publisher)) {
            throw new DomainException("No rights to create post");
        }
        if($suggested->isRejected()) {
            throw new DomainException("Cannot create post from rejected suggested post");
        }
        if($suggested->isDeleted()) {
            throw new DomainException("Cannot create post from deleted suggested post");
        }
        $isEdited = $this->attachmentsAreDifferent($attachments, $suggested->attachments()->toArray())
                    || ($text !== $suggested->text());
        $addSignature = true;
        
        $hideSignature = $isEdited && $suggested->hideSignatureIfEdited();
        /* Пользователь, который предлагает пост, может выбрать не добавлять подпись(в вк нельзя так сделать) или скрыть, если пост был отредачен публикатором. */
        if(!$suggested->addSignature() || $hideSignature) {
            $addSignature = false;
        }
        $post = new Post($this, $publisher, $suggested->creator(), $text, null, $disableComments, $disableReactions, $attachments, $addSignature);
        $this->posts->add($post);
    }
    
    function attachmentsAreDifferent(array $attachments, array $attachments2): bool {
        foreach($attachments as $attachment) {
            $inArray = false;
            foreach($attachments2 as $attachment2) {
                if($attachment->id() === $attachment2->id()) {
                    $inArray = true;
                    break;
                }
            }
            if(!$inArray) {
                return true;
            }
        }
        return false;
    }
    
    function addManager(User $initiator, User $user, string $position, bool $showInContacts, bool $allowExternalActivity): void {
        if($position === "admin") {
            $criteria = Criteria::create()->where(Criteria::expr()->eq("position", "admin"));
            if(count($this->bans->matching($criteria) >= 20)) {
                throw new DomainException("Количество админов достигло максимума");
            }
        }
        elseif($position === "editor") {
            $criteria = Criteria::create()->where(Criteria::expr()->eq("position", "editor"));
            if(count($this->bans->matching($criteria) >= 20)) {
                throw new DomainException("Количество редакторов достигло максимума");
            }
        }
        if($position === "moder") {
            $criteria = Criteria::create()->where(Criteria::expr()->eq("position", "moder"));
            if(count($this->bans->matching($criteria) >= 20)) {
                throw new DomainException("Количество модераторов достигло максимума");
            }
        }
        
        if(!$this->isAdmin($initiator) && !$this->isOwner($initiator)) {
            throw new DomainException("No rights to add manager");
        }
        /*
         * Я не знаю стоит ли разрешать делать пользователя, который не является подписчиком, менеджером. Здесь это очень легко реальзовать, но как это реализовать в GUI?
         * Удобнее и логичнее всего назначать менеджера со списка подписчиков, поэтому мне кажется, что стоит запретить назначать менеджерами НЕ подписчиков.
         * Хотя я хочу, чтобы пользователь мог быть менеджером, даже если он не подписан. Поэтому нет смысла запрещать.
         * 
         * В вк можно назначить забаненного пользователя менеджером и бан сразу слетает. Я думаю сделать также, но есть проблема, если я хочу оставить бан для истории, то его
         * нельзя удалять из БД, но как сделать, чтобы он перестал быть активным? Наверное, мягко удалять
         */
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("banned", $user))
            ->andWhere(Criteria::expr()->gt("end", new \DateTime('now')))
            ->andWhere(Criteria::expr()->eq("uniqueValue", "active")) // Если значение НЕ "active", то бан точно неактивный, если "active", то бан не удалён, но может быть он закончился
            ->setMaxResults(1);
        
        $matched = $this->bans->matching($criteria);
        if(count($matched)) {
            $matched->delete();
        }
        /* Нельзя банить менеджеров, но в теории может быть такое, что бан будет создан после того как будет сделан поиск банов и не будет найдено ни одного бана и ПЕРЕД тем, 
         * как пользователь станет менеджером. То есть пользователь может быть забанен за мгновение до того, как он станет менеджером.
         * Я не знаю как сделать, чтобы это было невозможно, наверное, это будет возможно только в случае, если объединить Subscription, Manager
         * и Ban в один класс, но это будет сложно
         */
        $manager = new Manager($this, $user, $position, $showInContacts, $allowExternalActivity);
        $this->managers->add($manager);
    }
    
    /*
     * Админ может разжаловать или изменить полномочия других менеджеров, кроме владельца. С одной стороны это странно, потому что это слишком опасно, но с другой стороны это необходимо, потому что
     * если владелец не собирается делать ничего, то нужна должность, у которой будут все нужные полномочия для управления. Главное, что админ не сможет украсть страницу у владельца, потому что
     * только владелец может сделать владельцем кого-либо другого. Админом стоит делать только человека, которому доверяешь, если же не доаверяешь человеку, то делай его редактором.
     * Владелец может изменить полномочия других менеджеров и разжаловать их
     * 
     * Меня смущает изменение владельца. Если владельцем нужно сделать менеджера, то нужно либо удалить Manager(если владелец хранится в $owner), либо изменить 2 объекта Manager, в одном поменять
     * должность "owner" на "admin", а в другом "admin" на "owner" (если владелец хранится НЕ в $owner, а в Manager). Когда владелец хранится в $owner, то при назначении нового владельца, который
     * является менеджером, нужно удалить объект класса Manager из-за ненадобности. Если же владелец хранится в Manager, то получается, что объекта класса Manager, у которого position === 'owner',
     * нельзя изменить, также нужно следить за тем, чтобы был только один Manager на страницу, где position === "owner" . Я считаю, что "owner" - это не позиция, поэтому Manager не
     * подходит для хранения инфы о владельце.
     * То есть, получается, что лучше использовать первый подход, где владелец хранится в Page::$owner и при назначении менеджера нужно удалить ненужный объект Manager, потому что
     * владелец не может быть менеджером. Кстати в вк только менеджера можно сделать владельцем, мне кажется, что мне стоит сделать так же
     */
    function editManager(User $initiator, string $managerId, string $level, bool $showInContacts): void {
        $manager = $this->managers->get($managerId);
        if(!$manager) {
            throw new DomainException("Manager not found");
        }
        if(!$this->isAdmin($initiator) && !$this->owner->equals($initiator)) {
            throw new DomainException("No rights to edit manager");
        }
        $manager->changePosition($level, $showInContacts);
    }
    
    function changeOwner(User $initiator, User $newOwner): void {
        if(!$this->owner->equals($initiator)) {
            throw new DomainException("Only owner of page can change an owner of the page");
        }
        if(!$this->isManager($newOwner)) {
            throw new DomainException("User cannot became an owner if he is not a manager");
        }
        $this->owner = $newOwner; /* Я не вижу смысла выбрасывать исключение в случае, если $initiator === $newOwner, это уже проблема клиента*/
    }
    
    /*
     * Я думаю стоит разрешить банить не подписчиков
     */
    function banUser(User $initiator, User $target, ?int $minutes, string $reason, ?string $message): void {
        if(!$this->isManager($initiator)) {
            throw new DomainException("No rights to ban user");
        }
        
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("banned", $target));
            //->andWhere(Criteria::expr()->gt("end", new \DateTime('now')))
            //->andWhere(Criteria::expr()->eq("uniqueValue", "active"));
        
        $matched = $this->bans->matching($criteria);
        //echo $matched[0]->id();exit();

        if(count($matched)) {
            $end = $matched[0]->getEnd();
            //echo $end;exit();
            $matched = $matched[0];
            if($end && $end > new \DateTime('now')) {
                throw new \App\Application\Exceptions\UnprocessableRequestException(123, "User ".$target->id()." is already banned");
            }
            if($end && $end < new \DateTime('now')) {
                $matched->delete();
                //echo $matched->deletedAt();exit();
            }
        }
        if($this->isManager($target)) {
            throw new DomainException("Cannot ban manager");
        }
        $ban = new Ban($this, $initiator, $target, $minutes, $reason, $message);
        $this->bans->add($ban);
        
    }
    
    function isOwner(User $user): bool {
        return $this->owner->equals($user);
    }
    
    function name(): string {
        return $this->name;
    }
    
    function isAllowedForExternalActivity(): bool {
        return true;
    }

    function equals(self $page): bool {
        return $this->id === $page->id;
    }
    
    function isDeleted(): bool {
        return false;
    }
    
    function currentPicture(): ?PagePicture {
        $criteria = Criteria::create()
            ->orderBy(array('updatedAt' => Criteria::DESC))
            ->setMaxResults(1);
        /** @var ArrayCollection<string, PagePicture> $pagePictures */
        $pagePictures = $this->pictures;
        
        $matched = $pagePictures->matching($criteria);
        if(count($matched)) {
            $key = array_key_first($matched->toArray());
            return $matched[$key];
        } else {
            return null;
        }
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
        $manager = $this->getManager($user);
        if($manager && ($manager->position() === 'admin' || $manager->position() === 'moder')) {
            return true;
        }
        if($this->owner->equals($user)) {
            return true;
        }
        return false;
    }
    
    function isMember(User $user): bool {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("user", $user));
        
        /** @var ArrayCollection<string, Membership> $memberships */
        $memberships = $this->memberships;
        
        /** @var ArrayCollection <string, Membership> $match */
        $match = $memberships->matching($criteria);
         
        return $match->first() !== null;
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
}
