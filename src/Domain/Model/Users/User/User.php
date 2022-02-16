<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\User;

use App\Domain\Model\DomainException;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\Password;
use App\Domain\Model\Users\Photos\Photo;
use App\Domain\Model\Users\Post\Post;
use App\Domain\Model\Users\Post\Shared\Shared;
use Assert\Assertion;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use RuntimeException;
use Ulid\Ulid;
use App\Domain\Model\Users\Ban\Ban;
use App\Domain\Model\Users\ConnectionsList\ConnectionsList;
use App\Domain\Model\Users\Connection\Connection;
use App\Domain\Model\Users\Subscription\Subscription;
use App\Domain\Model\Users\Albums\Album;
use App\Domain\Model\Users\Videos\Video;
use App\Domain\Model\TempPhoto\TempPhoto;
use App\Domain\Model\Users\VideoPlaylist\VideoPlaylist;
use App\Domain\Model\Users\SavesCollection\SavesCollection;
use App\Domain\Model\Users\Photos\ProfilePicture\ProfilePicture;
use App\Domain\Model\Users\Photos\AlbumPhoto\AlbumPhoto;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Users\Photos\Cover\Cover;
use App\Domain\Model\Chats\Chat;

class User {
    use UserMainInfo;
    use EntityTrait;
    
    const MAX_FRIENDS_COUNT = 10000;
    const MAX_ALBUMS_COUNT = 10000;
    const MAX_VIDEOS_COUNT = 10000;
    const TEMP_PHOTOS_LIMIT = 100;
    const PROFILE_PICTURES_LIMIT = 1000;
    const COVERS_LIMIT = 100;
    
    const SHOW_WITHOUT_YEAR = 2;
    const SHOW = 1;
    const HIDE = 0;
    
    private bool $isActivated;
    /** @var mixed $info */
    private $info;
    private bool $isSuspended = false;
    private bool $isDisabled = false;
    private bool $isBlocked = false;
    private bool $isDeleted = false;
    
    private bool $isModer = false;
    
    private Username $username;
    private ?DateTime $deletedAt = null;
    private DateTime $lastRequestsCheck;
    private ProfilePrivacySettings $privacy;
    private Settings $settings;
    
    /** @var Collection<string, Album> $albums */
    private Collection $albums;
    /** @var Collection<string, VideoPlaylist> $videoPlaylists */
    private Collection $videoPlaylists;
    //private Collection $higherEducations, $secondaryEducations = null;
    /** @var Collection<string, Post> $posts */
    private Collection $posts;
    /** @var Collection<string, ProfilePicture> $pictures */
    private Collection $pictures;
    /** @var Collection<string, Cover> $covers */
    private Collection $covers;
    
    /** @var Collection<string, ConnectionsList> $connectionsLists */
    private Collection $connectionsLists;
    /** @var Collection<string, Connection> $connections */
    private Collection $connections;
    
    /** @var Collection<string, Video> $videos */
    private Collection $videos;
    /** @var Collection<string, Ban> $bans */
    private Collection $bans;
    /** @var Collection<string, TempPhoto> $tempPhotos */
    private Collection $tempPhotos;
    /** @var Collection<string, SavesCollection> $savesCollections */
    private Collection $savesCollections;
    
    /**
     * @param array<string> $roles
     */
    function __construct(
        string $email, Password $password, string $firstName, string $lastName,
        Username $username, string $gender, string $language, string $birthday, array $roles = ['user']
    ) {
        $this->setMainInfo($email, $password, $firstName, $lastName, $gender, $birthday, $roles);
        $this->id = (string)Ulid::generate(true); // Не уверен, что при добавлении u будет сохранена возможность сортировки ID по времени. Вроде бы останется
        $this->username = $username; // А может быть Username должен быть создан здесь, а не передан сюда?
        
        $this->videos = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->pictures = new ArrayCollection();
        $this->covers = new ArrayCollection();
        $this->albums = new ArrayCollection();
        $this->videoPlaylists = new ArrayCollection();
        $this->bans = new ArrayCollection();
        
        $this->connectionsLists = new ArrayCollection();
        $this->connections = new ArrayCollection();
        
        $this->savesCollections = new ArrayCollection();
        $this->savesCollections->add(new SavesCollection($this, "Favorites", "", []));
        /*
        // Такие вещи как стандартные альбомы не созданы пользователем. Каждый из стандартных альбомов - это часть интерфейса класса User.
        // Кастомный альбом - это НЕ часть интерфейса.
        // Можно сделать так, что ни один из стандартных альбомов не будет частью интерфейса. Но в таком случае нужно сделать так, чтобы, например, тот же PhotoService знал
        // только о классе DefaultAlbum, и чтобы PhotoService не был привязан к именам альбомов класса DefaultAlbum. То есть, чтобы PhotoService "не знал" что есть альбом
        // с сохранёнками, с фото со стены и так далее. Но в таком случае не должно быть никаких различий между, например, альбомом с сохранёнками и альбомом с фото со стены.
        // Нельзя будет реализовать логику типа: фото с альбома "сохранёнки" нельзя перемещать, а фото с альбома "фото со стены" можно.
        // Если реализовать такую логику, то это будет выглядеть так, будто эти объекты разного типа, а типом выступает имя, то есть получается так, что вместо того, чтобы
        // типом выступал сам класс, мы вместо этого используем имя как тип.
        // 
        // Почему я так думаю? Просто мне кажется, что когда объекты находятся в одной коллекции, то не должно быть такого, что, например, PhotoService, зная имя альбома
        // будет как-то иначе действовать. Предыдущее предложение можно понять не так, как я имел в виду. Сложно объснять это.
        // Проще будет так: когда объекту из коллекции можно дать любое имя, то не стоит к этому имени привязываться
        */
        $this->addDefaultConnectionsLists();
        
        $this->privacy = new ProfilePrivacySettings($this);
        $this->settings = new Settings($language, $this);
        
        $this->isActivated = true;
        $this->createdAt = new \DateTime('now');
        $this->lastRequestsCheck = new \DateTime('now');
    }
    
    function createChat(array $participants, string $type, ?string $text, string $frontKey): Chat {
        $chat = new Chat($this, $participants, $type, $text, $frontKey);
        return $chat;
    }
    
    public function lastRequestsCheck(): \DateTime {
        return $this->lastRequestsCheck;
    }
    
    function checkRequests(): void {
//        print_r(new \DateTime('now'));exit();
        $this->lastRequestsCheck = new \DateTime('now');
    }
//    function subscriptions(): Collection {
    
    function pictures(): Collection {
        return $this->pictures;
    }
    
    function covers(): Collection {
        return $this->covers;
    }
    
    function settings(): Settings {
        return $this->settings;
    }

    function changeLanguage(string $language): void {
        $this->settings->changeLanguage($language);
    }
    
    function changeTheme(int $theme): void {
        $this->settings->changeTheme($theme);
    }

    function createPage(string $name, string $description, string $subject): Page {
        return new Page($this, $name, $description, $subject);
    }
    
    function birthdayAppearance(): int {
        return 2;
    }
    
    function subscribeToPage(Page $page): \App\Domain\Model\Pages\Subscription\Subscription {
        if($page->isSubscriber($this)) {
            throw new \App\Domain\Model\DomainException("User already subscribed to page");
        }
        return new \App\Domain\Model\Pages\Subscription\Subscription($page, $this);
    }
    
    /**
     * @param array<mixed> $whoCanSee
     */
    function createSavesCollection(string $name, string $description, array $whoCanSee): void {
        $collection = new SavesCollection($this, $name, $description, $whoCanSee);
        $this->savesCollections[] = $collection;
    }
    
    function isConnectedWith(self $user): bool {
        if($this->equals($user)) {
            return false;
        }
        
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("user1Id", $user->id))
            ->orWhere(Criteria::expr()->eq("user2Id", $user->id))
            ->andWhere(Criteria::expr()->eq("isAccepted", true))
            ->setMaxResults(1);
        
        /** @var \Doctrine\Common\Collections\ArrayCollection<string, Connection> $connections */
        $connections = $this->connections;
        $result = $connections->matching($criteria);
        
        return (bool)$result->count();
    }

    function getPrivacySetting(string $name): \App\Domain\Model\Users\User\ProfileComplexPrivacySetting {
        return $this->privacy->getSettingByName($name);
    }
    
    function isGlobalManager(): bool {
        return false;
    }
    
    function hide(): void {
        $this->privacy->hide();
    }
    
    function unhide(): void {
        $this->privacy->unhide();
    }
    
    function hideAge(): void {
        
    }
    
    function showAge(): void {
        
    }
    
    function open(): void {
        
    }
    
    function close(): void {
        
    }
    
    function isActivated(): bool {
        return $this->isActivated;
    }
    
    /**
     * @param array<mixed> $privacy
     */
    function createPlaylist(string $name, string $description, array $privacy): void {
        $playlist = new VideoPlaylist($this, $name, $description, $privacy);
        $this->videoPlaylists->add($playlist);
        //return $playlist;
    }
    
    function getPlaylist(string $id): ?VideoPlaylist {
        return $this->videoPlaylists->get($id);
    }
    
    function createGroup(string $name, bool $private, bool $visible): \App\Domain\Model\Groups\Group\Group {
        return new \App\Domain\Model\Groups\Group\Group($this, $name, $private, $visible);
    }
    
    function currentPicture(): ?ProfilePicture {
        $criteria = Criteria::create()
            ->orderBy(array('updatedAt' => Criteria::DESC))
            ->setMaxResults(1);
        /** @var ArrayCollection<string, ProfilePicture> $profilePictures */
        $profilePictures = $this->pictures;
        
        $matched = $profilePictures->matching($criteria);
        if(count($matched)) {
            $key = array_key_first($matched->toArray());
            return $matched[$key];
        } else {
            return null;
        }
    }
    
    function currentCover(): ?Cover {
        $criteria = Criteria::create()->setMaxResults(1);
        /** @var ArrayCollection<string, Cover> $covers */
        $covers = $this->covers;
        
        $matched = $covers->matching($criteria);
        if(count($matched)) {
            $key = array_key_first($matched->toArray());
            return $matched[$key];
        } else {
            return null;
        }
    }
    
    function createConnectionsList(string $name): ConnectionsList {
        // проверка количества списков
        $list = new ConnectionsList($this, $name);
        $this->connectionsLists->add($list);
        return $list;
    }
    
    /**
     * @return ArrayCollection<string, ConnectionsList>
     */
    function connectionsLists(): ArrayCollection {
        /** @var ArrayCollection<string, ConnectionsList> $lists */
        $lists = $this->connectionsLists;
        return $lists;
    }
    
    /**
     * @param array<mixed> $whoCanSee
     * @param array<mixed> $whoCanComment
     */
    function createAlbum(string $name, string $description, array $whoCanSee, array $whoCanComment): Album
    {
//        if($this->albumsCount === 10000) {
//            throw new DomainException(Errors::LIMIT_REACHED, "Max amount of albums reached");
//        }
        $album = new Album($this, $name, $description, $whoCanSee, $whoCanComment);
        $this->albums[] = $album;
        
        return $album;
    }
    
    function createConnection(User $user): Connection {
        return new Connection($this, $user);
    }
    function subscribe(User $user): Subscription {
        return new Subscription($this, $user);
    }
    
    function ban(self $user): void { // $isPermanent) {
        if($this->inBlacklist($user)) {
            throw new DomainException("Cannot ban user {$user->id()} because this user is already banned");
        }
//        if($isPermanent && $minutes) {
//            throw new DomainException("If ban is permanent then minutes are not needed");
//        }
//        elseif(!$isPermanent && !$minutes) {
//            throw new DomainException("If ban is not permanent then minutes are needed");
//        }
        $this->bans->add(new Ban($this, $user));
    }
    
    function inBlacklist(self $user): bool {
        $now = new \DateTime('now');
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("banned", $user));
        
        /** @var ArrayCollection<string, Ban> $bans */
        $bans = $this->bans;
        
        $match = $bans->matching($criteria); 
        return (bool)count($match);
    }
    
    function isSubscribedOn(self $user): bool {
//        $criteria = Criteria::create()->where(Criteria::expr()->eq("userId", $user->id()));
//        
//        $match = $this->subscriptions->matching($criteria); 
//        return (bool)count($match);
        return false;
    }
    
    function getSubscription(self $user): bool {
        $criteria = Criteria::create()->where(Criteria::expr()->eq("userId", $user->id()));
        $result = $this->subscriptions->matching($criteria);

        return isset($result[0]) ? $result[0] : null;
    }
    
    function addPhotoToAlbum(string $albumId, AlbumPhoto $photo): void {
        $album = $this->albums->get($albumId);
        //print_r($album = $this->albums->getKeys());exit();
        if(!$album) {
            throw new DomainException("Album $albumId not found in albums collection of user {$this->id}");
        }
        elseif($album->isDeleted()) {
            throw new DomainException("Cannot add photo to album $albumId because this album in the trash");
        }
        /** @var \App\Domain\Model\Users\Albums\Album $album */
        $album->addPhoto($photo);
    }
    
    /**
     * @param array<mixed> $data
     */
    function editComplexPrivacySetting(string $name, array $data): void {
        $this->privacy->editComplexSetting($name, $data);
    }
    
    /*
    // Если передан $albumId, то $forPost должен быть false и наоборот, но это не обязательно, потому что это не приведёт ни к чему плохому, поэтому я эту проверку
    // добавлять не буду
    // Здесь проверяется количество временных фото, если их максимум, то фото не будет создано. Это плохо, потому что это фото могло быть создано для добавления в альбом
    // Есть следующие варианты решения проблемы:
    // 1. Если фото создается, чтобы потом добавить его в альбом, то сразу передаём ID альбома и в этом методе добавляем фото в альбом, а проверку на количество временных
    // фото здесь просто не делаем
    */
//    /**
//     * @param array<string> $versions
//     * @throws DomainException
//     */
//    function createPhoto(array $versions, ?string $albumId = null): Photo {
//        if($albumId) {
//            $photo = new Photo($this, $versions);
//            $album = $this->albums->get($albumId);
//            if(!$album) {
//                throw new DomainException("Cannot add photo to album $albumId, no such album found in albums collection of user {$this->id}");
//            } elseif($album->isDeleted()) {
//                throw new DomainException("Cannot add photo to album $albumId because this album in the trash");
//            }
//            $album->addPhoto($photo);
//            return $photo;
//        } else {
//            $criteria = Criteria::create()
//                ->andWhere(Criteria::expr()->eq("isTemp", true));
////                ->andWhere(Criteria::expr()->isNull("profilePicture")) 
////                ->andWhere(Criteria::expr()->isNull("album"))
////                ->andWhere(Criteria::expr()->isNull("commentId"))
////                ->andWhere(Criteria::expr()->isNull("post"));
//            
//            /** @var ArrayCollection<string, Photo> $photos */
//            $photos = $this->photos();
//            $tempPhotosCount = count($photos->matching($criteria));
//
//            if($tempPhotosCount >= self::TEMP_PHOTOS_LIMIT) {
//                throw new DomainException(sprintf("Limit %s of unallocated photos reached", self::TEMP_PHOTOS_LIMIT));
//            }
//            return new Photo($this, $versions);
//        }
//    }
    
//    /** @return ArrayCollection<string, Photo> $photos */
//    function photos(): ArrayCollection {
//        /** @var ArrayCollection<string, Photo> $photos */
//        $photos = $this->photos;
//        return $photos;
//    }
    
    /**
     * @param array<string> $versions
     */
    function createTempPhoto(array $versions): void {
        // Я не знаю стоит ли хранить в этом классе коллекцию фото для постов
        $this->tempPhotos->add(new \App\Domain\Model\TempPhoto\TempPhoto($this, $versions));
    }
    
    function getTempPhoto(string $id): \App\Domain\Model\TempPhoto\TempPhoto {
        $photo = $this->tempPhotos->get($id);
        if(!$photo) {
            throw new \App\Application\Exceptions\UnprocessableRequestException(111, "'Temp' photo $id not found");
        }
        return $photo;
    }
    
    /**
     * @param array<string> $versions
     */
    function createPostPhoto(array $versions): \App\Domain\Model\Users\Post\Photo\Photo {
        // Я не знаю стоит ли хранить в этом классе коллекцию фото для постов
        return new \App\Domain\Model\Users\Post\Photo\Photo($this, $versions);
    }
    
    /**
     * 
     * @param array<string> $versions
     */
    function createCommentPhoto(array $versions): \App\Domain\Model\Users\Comments\Photo\Photo {
        // Я не знаю стоит ли хранить в этом классе коллекцию фото для постов
        //print_r($versions);exit();
        return new \App\Domain\Model\Users\Comments\Photo\Photo($this, $versions);
    }
    
    /**
     * @param array<string> $versions
     * @throws DomainException
     */
    function createPicture(array $versions): ProfilePicture {
        if($this->pictures->count() >= self::PROFILE_PICTURES_LIMIT) {
            throw new DomainException(sprintf("Limit %s of profile pictures reached", self::PROFILE_PICTURES_LIMIT));
        }
        //print_r($versions);exit();
        $picture = new ProfilePicture($this, $versions);
        $this->pictures->add($picture);
        return $picture;
    }
    
    /**
     * @param array<array> $versions
     * @throws DomainException
     */
    function createCover(array $versions): Cover{
        if($this->covers->count() >= self::COVERS_LIMIT) {
            throw new DomainException(sprintf("Limit %s of covers reached", self::COVERS_LIMIT));
        }
        $cover = new Cover($this, $versions);
        $this->covers->add($cover);
        return $cover;
    }
    
    /**
     * @param array<string> $previews
     * @param array<mixed> $whoCanSee
     * @param array<mixed> $whoCanComment
     */
    function createVideo(string $name, string $description, string $link, string $hosting, array $previews, array $whoCanSee, array $whoCanComment): Video {
        $video = new Video($this, $name, $description, $link, $hosting, $previews, $whoCanSee, $whoCanComment);
        //$this->videos->add($video);
        return $video;
    }
    
    /**
     * @param array<mixed> $attachments
     */
    function createPost(string $text, bool $disableComments, bool $disableReactions, bool $public, ?\App\Domain\Model\Common\Shares\Shared $shared, array $attachments): Post
    {
        /* Я не знаю стоит ли разрешить делиться своим постом, когда пост не является публичным. Я точно знаю, что делиться своим постом нужно разрешить.
         * Если запретить делиться своим непубличным постом, то где делать этот запрет? Здесь это уместно, но как-то некрасиво.
         */
        
//        if($shared && $shared->shared() instanceof Post && !$shared->shared()->isPublic()) {
//            throw new DomainException("Cannot shared own non public post");
//        }
        $post = new Post($this, $text, $shared, $disableComments, $disableReactions, $public, $attachments);
        $this->posts->add($post);
        return $post;
    }
    
//    function addFriendToList(Friendship $friendship, string $listName) {
//        /** @var FriendsList $list */
//        //$list = $this->friendsLists->get($listName);
//        
//        $criteria = Criteria::create()
//            ->where(Criteria::expr()->eq("name", $listName));
//        
//        $list = $this->friendsLists->matching($criteria)[0];
//
//        $list->addFriendship($friendship);
//    }
//    
//    function getFriendsList(string $name): FriendsList {
//        $criteria = Criteria::create()
//            ->where(Criteria::expr()->eq("name", $name));
//        
//        $list = $this->connectionsLists->matching($criteria)[0];
//        
//        return $list;
//    }

    private function addDefaultConnectionsLists(): void {
        $this->connectionsLists->add(new ConnectionsList($this, 'best_friends', true));
        $this->connectionsLists->add(new ConnectionsList($this, 'colleagues', true));
        $this->connectionsLists->add(new ConnectionsList($this, 'school_friends', true));
        $this->connectionsLists->add(new ConnectionsList($this, 'university_friends', true));
        $this->connectionsLists->add(new ConnectionsList($this, 'relatives', true));
        $this->connectionsLists->add(new ConnectionsList($this, 'acquaintances', true));
    }
    
    function delete(DateTime $deletedAt): void {
        $this->deletedAt = $deletedAt;
    }

    function isDeleted(): bool { return false; }
    function isAdmin(): bool { return false; }
    function isModer(): bool { return false; }
    function isSuspended(): bool { return $this->isSuspended; } 
    function isDisabled(): bool { return $this->isDisabled; }
    function isBlocked(): bool { return $this->isBlocked; }
    function username(): Username { return $this->username; }
    function equals(User $user): bool { return $this->id === $user->id; }
    function changeUsername(string $username): void {
        $this->username = new Username($username);
    }     
    
    function fullname(): string { return "{$this->firstName} {$this->lastName}"; }
    function profileSettings(): Settings { return $this->settings; }
    //function isOnline(): bool { return $this->isOnline; }
    //function changeIsOnline(string $value) { $this->isOnline = $value ? true : false; }

//    
//    function addUserToBlacklist(UserId $userId): void {
//        if($this->isHimself($userId)) {
//            $arr = ['code' => '1', 'message' => 'Cannot ban himself'];
//        } elseif($this->inBannedList($userId)) {
//            $arr = ['code' => '2', 'message' => "Already banned"];
//        }
//        if(isset($arr)) {
//            throw new RuntimeException(\json_encode($arr));
//        }
//        $this->banned[$userId] = $userId;
//    }
    
    //function isFriend(UserId $userId): bool { return \in_array($userId, $this->friendsIds); }
    //function isSubscriber(int $userId): bool { return \in_array($userId, $this->subscribers); }
    //function isSubscribedOn(int $userId): bool { return \in_array($userId, $this->subscriptions); }
    //function bannedUsers(): array {  return $this->banned; }
    
//    function unban(User $user): void {
//        if(!$this->inBlacklist($user)) {
//            throw new RuntimeException("User {$user->id} was not banned");
//        }
//        unset($this->banned[$user->id]);
//    }
    
    /**
     * @return mixed
     */
    function createDialog(User $interlocutor, string $firstMessage = null) {                // То, не создан ли уже такой диалог, будет проверяться в App. Service. Также репозиторий выбросит исключение если
//        if($this->inBannedList($interlocutor->id)) {                                    // попытаться туда сохранить повторяющийся ID
//            throw new UserIsBannedException("User {$interlocutor->id} is banned");
//        } elseif($interlocutor->inBannedList($this->id)) { 
//            throw new BannedByUserException("Banned by user {$interlocutor->id}");
//        } elseif(!$this->isHimself($interlocutor->id)
//            && !$interlocutor->isAllowedByPrivacyTo($this, Action::START_DIALOG)
//        ) {
//            //throw new PrivacyException("Prohibited by user {$interlocutor->id} privacy");
//        }
//        //$dialogId = $this->createDialogId($this->id, $interlocutor->id);
//
//        $dialog = new Dialogue($this->id, [$this, $interlocutor]);
//        
//        if($firstMessage) { $dialog->createMessage($this->id, $firstMessage); }
//        return $dialog;
    }
    
   // function inBannedList(UserId $userId): bool { return \in_array($userId, $this->banned); } // Преобразование $userId в string происходит где-то в in_array

    function createDialogId(string $firstId, string $secondId): string {
        if($firstId < $secondId) {
            return "{$firstId}-{$secondId}";
        } else {
            return "{$secondId}-{$firstId}";
        }
    }

    function changeStatus(string $status): void {
        Assertion::maxLength($status, 300);
        $kek = $status;
    }
    
    function isDeactivated(): bool {
        return false;
    }

    //function status(): string { return $this->status; }
    //function changeCountry(string $country) : void { $this->country = $country; }
    //function country() { return $this->country; }
    //function changeCity(string $city) : void { $this->city = $city; }
    //function city() { return $this->city; }
    //function changeWork($work) : void { $this->work = $work; }
    //function work() { return $this->work; }
    function isHidden(): bool { return $this->privacy->isHidden(); }
    //function revealProfile(): void { $this->privacy->open(); }
    //function hideProfile(): void { $this->privacy->close(); }
    //function isClosed(): bool { return $this->privacy->isClosed(); }
    function privacy(): ProfilePrivacySettings { return $this->privacy; }

//    function closeProfile() : void {
//        //$this->privacy->closeFromNonFriends();
//        $this->privacy->close();
//    }
//    
//    function openProfile() : void {
//        //$this->privacy->openForNonFriends();
//        $this->privacy->open();
//    }
    
//    function changePrivacySetting(string $settingName, int $accessLevel, array $allowedFriendships, array $unallowedFriendships, array $allowedListsIds, array $unallowedListsIds): void {
//        $criteria = Criteria::create() // возможно стоит вынести этот кода отсюда
//            ->where(Criteria::expr()->in("status", $allowedListsIds))
//            ->setMaxResults(1);
//        $allowedLists = $this->connectionsLists->matching($criteria);
//        
//        $criteria2 = Criteria::create() // возможно стоит вынести этот кода отсюда
//            ->where(Criteria::expr()->in("status", $unallowedListsIds))
//            ->setMaxResults(1);
//        $unallowedLists = $this->connectionsLists->matching($criteria2);
//        
//        $this->privacy->updateSetting($settingName, $accessLevel, $allowedFriendships, $unallowedFriendships, $allowedLists, $unallowedLists);
//    }
}