<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\PrivacyService;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\AccessLevels as AL;
use App\Domain\Model\Users\Privacy;
use App\Domain\Model\Users\User\ProfilePrivacySettings as PS;
use App\Domain\Model\Users\Albums\Album;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use App\Domain\Model\Users\ComplexPrivacySetting;
use App\Domain\Model\Users\Videos\Video;
use App\Domain\Model\Users\Photos\Photo;
use App\Domain\Model\Users\User\UserRepository;

final class PrivacyResolver {
    
    private ConnectionRepository $connections;
    private UserRepository $users;
    
    public function __construct(ConnectionRepository $connections, UserRepository $users) {
        $this->connections = $connections;
        $this->users = $users;
    }
    
    function isAllowedByPrivacy(User $requestee, User $requester, string $settingName, ConnectionRepository $friendships): bool {        
        
        // Возможно стоит убрать это исключение и положиться на ошибку, которая будет выброшена при получении элемента из $setting по неправильному ключу $settingName
        if(!\in_array($settingName, PS::complexSettingsNames())) {
            throw new \InvalidArgumentException("There is no setting '$settingName' in profile privacy settings");
        }
        if($requestee->equals($requester)) {
            return true;
        }
        $setting = $requestee->privacy()->getSettingByName($settingName);
        
        return $this->hasAccess($setting, $requester, $this->connections);
    }
    
    function canGuestSeeAlbum(Album $album): bool {
//        $albumCreator = $album->creator();
//
//        return !$albumCreator->isClosed()
//               && !$albumCreator->isHidden()
//               && $album->whoCanSee()->accessLevel() === AL::EVERYONE;
        return false;
    }
    
    function canSeeVideo(User $requester, Video $video, ConnectionRepository $connections): bool {
        if($video->owner()->equals($requester)) {
            return true;
        }
        return $this->hasAccess($video->whoCanSee(), $requester, $connections);
    }
    
    function canSeeAlbum(User $requester, Album $album, ConnectionRepository $connections): bool {
        if($album->user()->equals($requester)) {
            return true;
        }
        return $this->hasAccess($album->whoCanSee(), $requester, $connections);
    }
    /**
    function hasAccess_old(ComplexPrivacySetting $setting, User $requestee, User $requester, ConnectionRepository $connections): bool {
        if($requestee->equals($requester)) {
            throw new \InvalidArgumentException("Same users passed"); // Есть вещи, которые всегда запрещены для владельца, например создание Friendship или Membership, мне кажется, что сервис
            // авторизации должен не допустить того, что здесь будут одинаковые $requestee и $requester
        }
        
        $areFriends = (bool)$connections->getByUsersIds($requester->id(), $requestee->id());
        
        if($setting->isRestrictedWhenProfileClosed() && $requestee->isClosed() && !$areFriends) { // Если профиль закрыт, то false возвращается только в случае, если уровень доступа
            // в настройке уменьшается при закрытом профиле
            return false;
        }
        $al = $setting->accessLevel();
        switch ($al) {
            case AL::EVERYONE:
                return !$this->isUnallowed($requester, $setting);
            case AL::CONNECTIONS:
                return $areFriends && !$this->isUnallowed($requester, $setting);
            case AL::CONNECTIONS_OF_CONNECTIONS:
                $haveCommonFriend = $this->haveCommonFriends($requester, $requestee, $connections);
                return ($areFriends || $haveCommonFriend) && !$this->isUnallowed($requester, $setting);
            case AL::SOME_CONNECTIONS_AND_LISTS:
                return $this->isAllowed($requester, $setting) && !$this->isUnallowed($requester, $setting);             // Если пользователь находится в списке разрешенных, то он точно является другом
            case AL::NOBODY:
                return false;
            default:
                throw new \InvalidArgumentException("Wrong access level passed($al)");
        }
    }
    */
    
    function hasAccess(ComplexPrivacySetting $setting, User $requester, ConnectionRepository $connections): bool {
        $owner = $this->users->getById($setting->ownerId());
        
        if(!$owner) {
            throw new \LogicException("User {$setting->ownerId()} not found");
        }
        
        if($owner->equals($requester)) {
            throw new \InvalidArgumentException("Same users passed"); // Есть вещи, которые всегда запрещены для владельца, например создание Friendship или Membership, мне кажется, что сервис
            // авторизации должен не допустить того, что здесь будут одинаковые $owner и $requester
        }
        
        $areFriends = (bool)$connections->getByUsersIds($requester->id(), $owner->id());
        
//        if($setting->isRestrictedWhenProfileClosed() && $owner->isClosed() && !$areFriends) { // Если профиль закрыт, то false возвращается только в случае, если уровень доступа
//            // в настройке уменьшается при закрытом профиле
//            return false;
//        }
        $al = $setting->accessLevel();
        switch ($al) {
            case AL::EVERYONE:
                return !$this->isUnallowed($requester, $setting);
            case AL::CONNECTIONS:
                return $areFriends && !$this->isUnallowed($requester, $setting);
            case AL::CONNECTIONS_OF_CONNECTIONS:
                $haveCommonFriend = $this->haveCommonFriends($requester, $owner, $connections);
                return ($areFriends || $haveCommonFriend) && !$this->isUnallowed($requester, $setting);
            case AL::SOME_CONNECTIONS_AND_LISTS:
                return $this->isAllowed($requester, $setting) && !$this->isUnallowed($requester, $setting);             // Если пользователь находится в списке разрешенных, то он точно является другом
            case AL::NOBODY:
                return false;
            default:
                throw new \InvalidArgumentException("Wrong access level passed($al)");
        }
    }
    
    private function haveCommonFriends(User $requester, User $requestee, ConnectionRepository $friendships): bool {
        return $friendships->haveCommonFriend($requester->id(), $requestee->id());
    }

    private function isUnallowed(User $requester, ComplexPrivacySetting $setting): bool {
        return $this->inUnallowedUsers($requester, $setting) || $this->inUnallowedList($requester, $setting);
    }
    
    private function inUnallowedUsers(User $requester, ComplexPrivacySetting $setting): bool {
        return in_array((string)$requester->id(), $setting->unallowedConnections()->toArray());
    }

    private function inUnallowedList(User $requester, ComplexPrivacySetting $setting): bool {
        foreach($setting->unallowedLists() as $list) {
            foreach ($list->connections() as $connection) {
                if($connection->initiatorId() === $requester->id() || $connection->targetId() === $requester->id()) {
                    return true;
                }
            }
        }
        return false;
    }
    
    private function isAllowed(User $requester, ComplexPrivacySetting $setting): bool {
        return $this->inAllowedUsers($requester, $setting) || $this->inAllowedList($requester, $setting);
    }

    private function inAllowedUsers(User $requester, ComplexPrivacySetting $setting): bool {
        return in_array((string)$requester->id(), $setting->unallowedConnections()->toArray());
    }

    private function inAllowedList(User $requester, ComplexPrivacySetting $setting): bool {
        foreach($setting->allowedLists() as $list) {
            foreach ($list->connections() as $connection) {
                if($connection->initiatorId() === $requester->id() || $connection->targetId() === $requester->id()) {
                    return true;
                }
            }
        }
        return false;
    }
}
