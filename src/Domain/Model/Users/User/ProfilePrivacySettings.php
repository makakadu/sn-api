<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\User;

use App\Domain\Model\Users\Privacy;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\AccessLevels as AL;
use Assert\Assertion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Users\Connection\Connection;
use App\Domain\Model\Users\ConnectionsList\ConnectionsList;
use App\Domain\Model\DomainException;

class ProfilePrivacySettings extends Privacy {
    const START_DIALOG = 'start_dialog';
    const INVITE_TO_GROUP = 'invite_to_group';
    const TAG_ON_PHOTO = 'tag_on_photo';
    
    const PICTURES = 'pictures'; /* Если картинки профиля защищены настройками приватности от пользователя, то пользователь может видеть только текущую картинку в маленьком
     * разрешении
     */
    const COMMENT_POSTS = 'comment_posts';
    const INFO = 'info';
    const TAGGED_VIDEOS = 'tagged_videos';
    const TAGGED_PHOTOS = 'tagged_photos';
    const GROUPS = 'groups_list';
    const PAGES_SUBSCRIPTIONS = 'pages_subscriptions';
    const PROFILES_SUBSCRIPTIONS = 'profiles_subscriptions';
    const HIDDEN_CONNECTIONS = 'hidden_friends';
    const COMMENT_PICTURES = 'comment_pictures';
    
    private bool $isAgeVisible = true;
    private bool $isHidden = false;
    //private bool $notifyAboutAllConnectionRequests = true; // Мне кажется, что это не смахивает на настройку приватности
    
    /**
     * @var Collection<string, ProfileComplexPrivacySetting> $complexSettings
     */
    private Collection $complexSettings;
    protected User $user;

    function __construct(User $user) {
        $this->user = $user;
        
        $this->complexSettings = new ArrayCollection();
        /*
         Я считаю, что нельзя всем запрещать начинать диалог, я считаю, что нельзя запрещать начинать диалог никому из connections.
         Я считаю, что нужно запрещать кому-то писать сообщения на уровне диалога, а не на уровне настроек приватности. В настройках приватности будут только 3 уровня и это только доступ к 
         созданию диалог. Нужно добавить в диалог свойств, которое будет запрещать создавать сообщения в этом диалоге, если оно будет true. Мне кажется это киллер фича, можно заморозить 
         диалог прям в меню диалога. Чтобы в вк заморозить диалог (если он уже начался) нужно добавить человека в ЧС. Вообще мне кажется бредовой идеей создавать список людей, которые могут
         начать диалог. Вполне достаточно 3 уровня доступа к этой возможности.
         */
        $this->complexSettings->add(new ProfileComplexPrivacySetting($this, self::START_DIALOG, AL::EVERYONE, [AL::CONNECTIONS, AL::CONNECTIONS_OF_CONNECTIONS, AL::EVERYONE]));
        $this->complexSettings->add(new ProfileComplexPrivacySetting($this, self::INVITE_TO_GROUP, AL::EVERYONE));
        $this->complexSettings->add(new ProfileComplexPrivacySetting($this, self::TAG_ON_PHOTO, AL::EVERYONE));
        $this->complexSettings->add(new ProfileComplexPrivacySetting($this, self::PICTURES, AL::EVERYONE));
        $this->complexSettings->add(new ProfileComplexPrivacySetting($this, self::COMMENT_POSTS, AL::EVERYONE));
        // Нет смысла в том, чтобы показывать скрытых друзей всем, ведь тогда они не будут скрытыми
        $this->complexSettings->add(new ProfileComplexPrivacySetting($this, self::HIDDEN_CONNECTIONS, AL::NOBODY, [AL::NOBODY, AL::SOME_CONNECTIONS_AND_LISTS, AL::CONNECTIONS]));
        $this->complexSettings->add(new ProfileComplexPrivacySetting($this, self::INFO, AL::EVERYONE));
        $this->complexSettings->add(new ProfileComplexPrivacySetting($this, self::GROUPS, AL::EVERYONE));
        $this->complexSettings->add(new ProfileComplexPrivacySetting($this, self::TAGGED_VIDEOS, AL::EVERYONE));
        $this->complexSettings->add(new ProfileComplexPrivacySetting($this, self::TAGGED_PHOTOS, AL::EVERYONE));
        $this->complexSettings->add(new ProfileComplexPrivacySetting($this, self::COMMENT_PICTURES, AL::EVERYONE));
    }
    
    function user(): User {
        return $this->user;
    }

    /**
     * @param array<mixed> $data
     * @throws \InvalidArgumentException
     */
    function editComplexSetting(string $name, array $data): void {
        $setting = $this->complexSettings->get($name);
        if(!$setting) {
            throw new \InvalidArgumentException("There is no complex setting with name '$name' in the privacy settings");
        }
        $allowedConnections = $data['lists']['allowed_connections'];
        $unallowedConnections = $data['lists']['unallowed_connections'];
        $allowedLists = $data['lists']['allowed_lists'];
        $unallowedLists = $data['lists']['unallowed_lists'];
        
        $this->failIfUserIsNotParticipantOfConnection($allowedConnections);
        $this->failIfUserIsNotParticipantOfConnection($unallowedConnections);
        $this->failIfUserIsNotOwnerOfConnectionsList($allowedLists);
        $this->failIfUserIsNotOwnerOfConnectionsList($unallowedLists);
        
        $setting->edit($data['access_level'], $allowedConnections, $unallowedConnections, $allowedLists, $unallowedLists);
    }
    
    /**
     * @param array<Connection> $connections
     */
    function failIfUserIsNotParticipantOfConnection(array $connections): void {
        foreach ($connections as $conn) {
            $userId = $this->user->id();
            if($userId !== $conn->initiatorId() && $userId !== $conn->targetId() ) {
                throw new DomainException("Incorrect privacy data, profile owner($userId) is not participant of connection {$conn->id()}");
            }
        }
    }
    
    /**
     * @param array<ConnectionsList> $connectionsLists
     */
    function failIfUserIsNotOwnerOfConnectionsList(array $connectionsLists): void {
        /** @var ConnectionsList $list */
        foreach ($connectionsLists as $list) {
            if(!$this->user->equals($list->user())) {
                throw new DomainException("Incorrect privacy data, profile owner({$this->user->id()}) is not owner of connections list {$list->id()}");
            }
        }
    }
    
    /**
     * @return array<string>
     */
    static function complexSettingsNames(): array {
        $reflected = new \ReflectionClass(__CLASS__);
        /* @phpstan-ignore-next-line */
        return array_diff($reflected->getConstants(), $reflected->getParentClass()->getConstants());
    }

    function failIfAccessLevelIsNotAppropriate(string $settingName, int $al): void {
        $this->failIfOutOfRange($al);
        
        switch ($settingName) {
            case self::START_DIALOG:
                if($al < AL::EVERYONE && $al > AL::SOME_CONNECTIONS_AND_LISTS) {
                    $this->throwAccessLevelIsNotAllowedException($al, $settingName);
                }
            case self::TAG_ON_PHOTO:
                if($al < AL::SOME_CONNECTIONS_AND_LISTS || $al > AL::CONNECTIONS) {
                    $this->throwAccessLevelIsNotAllowedException($al, $settingName);
                }
//            case self::OFFER_CONNECTION:
//                if($al !== AL::EVERYONE && $al !== AL::CONNECTIONS_OF_CONNECTIONS) {
//                    $this->throwAccessLevelIsNotAllowedException($al, $settingName);
//                }
            case self::HIDDEN_CONNECTIONS:
                if($al === AL::EVERYONE) {
                    $this->throwAccessLevelIsNotAllowedException($al, $settingName);
                }
        }
    }
  
    function id(): int { return $this->id; }
    
    function hideAge(): void {
       $this->isAgeVisible = false;
    }
    
    function showAge(): void {
       $this->isAgeVisible = true;
    }
    
//    function open(): void {
//        $this->isClosed = false;
//    }
//    
//    function close(): void {
//        $this->isClosed = true;
//    }
    
    function hide(): void {
        $this->isHidden = true;
    }
    
    function unhide(): void {
        $this->isHidden = false;
    }
    
//    function isClosed(): bool { return $this->isClosed; }
    function isHidden(): bool { return $this->isHidden; }
    function isAgeHidden(): bool { return true; }

    /** @return array<mixed> */
    function whoCanStartDialog(): array {
        return [];
    }
    
    /** @return array<mixed> */
    function settingToArray(ProfileComplexPrivacySetting $setting): array {
        return [
            'access_level' => $setting->accessLevel(),
            'allowed_conns' => $setting->allowedConnections(),
            'unallowed_conns' => $setting->unallowedConnections(),
            'allowed_lists' => $setting->allowedLists(),
            'unallowed_lists' => $setting->unallowedLists(),
        ];
    }
    
    /**
     * @throws \LogicException
     */
    function getSettingByName(string $name): ?ProfileComplexPrivacySetting {
        $setting = $this->complexSettings->get($name);
        if(!$setting) {
            return null;
            //throw new \LogicException("Complex privacy setting '$name' not found in collection 'complexSettings'");
        }
        return $setting;
    }
    
//    /** @return array<mixed> */
//    function whoCanTagOnPhoto(): array {
//        $name = self::TAG_ON_PHOTO;
//        $setting = $this->complexSettings->get($name);
//        if(!$setting) {
//            throw new \LogicException("Complex privacy setting '$name' not found in collection 'complexSettings'");
//        }
//        return $this->settingToArray($setting);
//    }
//    
//    /** @return array<mixed> */
//    function whoCanOfferFriendship(): array {
//        return [];
//    }
//
//    /** @return array<mixed> */    
//    function whoSeeHiddenFriends(): array {
//        $name = self::HIDDEN_CONNECTIONS;
//        $setting = $this->complexSettings->get($name);
//        if(!$setting) {
//            throw new \LogicException("Complex privacy setting '$name' not found in collection 'complexSettings'");
//        }
//        return $this->settingToArray($setting);
//    }
//    
//    /** @return array<mixed> */
//    function whoCanInviteToGroup(): array {
//        $name = self::INVITE_TO_GROUP;
//        $setting = $this->complexSettings->get($name);
//        if(!$setting) {
//            throw new \LogicException("Complex privacy setting '$name' not found in collection 'complexSettings'");
//        }
//        return $this->settingToArray($setting);
//    }
//    
//    /** @return array<mixed> */
//    function whoCanCommentPosts(): array {
//        $name = self::COMMENT_POSTS;
//        $setting = $this->complexSettings->get($name);
//        if(!$setting) {
//            throw new \LogicException("Complex privacy setting '$name' not found in collection 'complexSettings'");
//        }
//        return $this->settingToArray($setting);
//    }
//    
//    /** @return array<mixed> */
//    function whoSeeInfo(): array {
//        $name = self::INFO;
//        $setting = $this->complexSettings->get($name);
//        if(!$setting) {
//            throw new \LogicException("Complex privacy setting '$name' not found in collection 'complexSettings'");
//        }
//        return $this->settingToArray($setting);
//    }
//    
//    /** @return array<mixed> */
//    function whoSeeAudio(): array {
//        return [];
//    }
//    
//    /** @return array<mixed> */
//    function whoSeeVideos(): array {
//        return [];
//    }
//    
//    /** @return array<mixed> */
//    function whoSeeTaggedPhotos(): array {
//        $name = self::TAGGED_PHOTOS;
//        $setting = $this->complexSettings->get($name);
//        if(!$setting) {
//            throw new \LogicException("Complex privacy setting '$name' not found in collection 'complexSettings'");
//        }
//        return $this->settingToArray($setting);
//    }
//    
//    /** @return array<mixed> */
//    function whoSeeGroupsList(): array {
//        $name = self::GROUPS;
//        $setting = $this->complexSettings->get($name);
//        if(!$setting) {
//            throw new \LogicException("Complex privacy setting '$name' not found in collection 'complexSettings'");
//        }
//        return $this->settingToArray($setting);
//    }
    
}
