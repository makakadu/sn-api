<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\PrivacyService;

use App\Domain\Model\Users\AccessLevels as AL;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Privacy;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use App\Domain\Model\Users\ConnectionsList\ConnectionsListRepository;
use App\Domain\Model\Users\Videos\Video;
use Assert\Assertion;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\Exceptions\MalformedRequestException;
use App\Domain\Model\Users\ComplexPrivacySetting;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\ConnectionsList\ConnectionsList;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Users\Connection\Connection;

class PrivacyResolver_new {
    
    private ConnectionRepository $connections;
    private ConnectionsListRepository $connectionsLists;
    private UserRepository $users;
    
    function __construct(ConnectionRepository $connections, ConnectionsListRepository $connectionsLists, UserRepository $users) {
        $this->connections = $connections;
        $this->connectionsLists = $connectionsLists;
        $this->users = $users;
    }
    
    function hasAccess(User $requester, ComplexPrivacySetting $setting): bool {
        $owner = $this->users->getById($setting->ownerId());
        
        if(!$owner) {
            throw new \LogicException("User {$setting->ownerId()} not found");
        }
        /*
         Мне кажется, что лучше всего не вызывать этот метод, если $requester является владельцем $setting, то есть, если $requester владелец $setting. Всё из-за
         того, что есть некоторые настройки, например invite_to_group, которые нет смысла проверять, потому что пользователь не может пригласить сам себя.
         Настройки приватности не распостраняются на владельца
         Если здесь будет код, который возвращает true в случае, если $requester - это владелец, то это будет плохо, потому что есть некоторые действия и ресурсы, доступ
         к которым невозможен со стороны владельца, например, offer_connection, если возвратится true, то это будет значит, что пользователь может предложить себе connection,
         что абсолютно не логично и не уместно.
        */
        if($owner->equals($requester)) {
            throw new \InvalidArgumentException("Requester should not be owner of setting"); // Ошибка, которой быть не должно, если она есть, то вызывающий этот метод код содержит
            // ошибку(или ошибки)
        }
        
        $areFriends = (bool)$this->connections->getByUsersIds($requester->id(), $owner->id());

        $al = $setting->accessLevel();
        switch ($al) {
            case AL::EVERYONE:
                return !$this->isUnallowed($requester, $setting);
            case AL::CONNECTIONS:
                return $areFriends && !$this->isUnallowed($requester, $setting);
            case AL::CONNECTIONS_OF_CONNECTIONS:
                $haveCommonFriend = $this->haveCommonFriends($requester, $owner, $this->connections);
                return ($areFriends || $haveCommonFriend) && !$this->isUnallowed($requester, $setting);
            case AL::SOME_CONNECTIONS_AND_LISTS:
                return $this->isAllowed($requester, $setting) && !$this->isUnallowed($requester, $setting);             // Если пользователь находится в списке разрешенных, то он точно является другом
            case AL::NOBODY: // Мне кажется, что это полная дичь, это ведь соц сеть, зачем разрешать пользователю скрывать что-либо от всех, чтобы только он мог видеть это?
                return false; // Хотя пускай будет
            default:
                throw new \InvalidArgumentException("Wrong access level passed($al)");
        }
    }
    
    function guestsHaveAccessToProfile(User $user): bool {
        return !$user->isHidden();
    }
    
    function guestsHaveAccess(ComplexPrivacySetting $setting): bool {
        return $setting->accessLevel() === AL::EVERYONE;
    }
    
    function areUsersFriends(User $user1, User $user2): bool {
        return (bool)$this->connections->getByUsersIds($user1->id(), $user2->id());
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
    
    /**
     * @param array<string> $connectionsIds
     * @return ArrayCollection<int, Connection>
     */
    function findConnections(array $connectionsIds): ArrayCollection {
        $connections = $this->connections->getByIds($connectionsIds);
        $prepared = new ArrayCollection();
        foreach ($connections as $connection) {
            $prepared[] = $connection;
        }
        return $prepared;
    }
    
    /**
     * @param array<string> $listsIds
     * @return array<ConnectionsList>
     */
    function findConnectionsLists(array $listsIds): array {
        return $this->connectionsLists->getByIds($listsIds);
    }
    
    /**
     * @param array<array> $lists
     * @return array<mixed>
     */
    function prepareLists(array $lists): array {
        $allowedConnections = isset($lists['allowed_connections']) ? $lists['allowed_connections'] : [];
        $unallowedConnections = isset($lists['unallowed_connections']) ? $lists['unallowed_connections'] : [];
        $allowedLists = isset($lists['allowed_lists']) ? $lists['allowed_lists'] : [];
        $unallowedLists = isset($lists['unallowed_lists']) ? $lists['unallowed_lists'] : [];
        
        $prepared = [
            'allowed_connections' => $this->findConnections($allowedConnections),
            'unallowed_connections' => $this->findConnections($unallowedConnections),
            'unallowed_lists' => $this->findConnectionsLists($unallowedLists),
            'allowed_lists' => $this->findConnectionsLists($allowedLists),
            
        ];
        return $prepared;
    }
    
    /**
     * @param mixed $privacySettingData
     * @throws MalformedRequestException
     * @throws UnprocessableRequestException
     */
    function checkPrivacyData(string $privacySettingName, $privacySettingData): void {
        try {
            Assertion::isArray($privacySettingData, "'$privacySettingName' parameter should be an array");
            Assertion::keyExists($privacySettingData, 'access_level', "Incorrect structure of '$privacySettingName' param, '$privacySettingName' should contain 'access_level' property");
            Assertion::integer($privacySettingData['access_level'], "Incorrect structure of '$privacySettingName' param, property 'access_level' should be an integer");

            Assertion::keyExists($privacySettingData, 'lists', "Incorrect structure of '$privacySettingName' param, '$privacySettingName' should contain 'lists' property");
            Assertion::isArray($privacySettingData['lists'], "Incorrect structure of '$privacySettingName' param, property 'lists' should be array");
            
            $allowedConnections   = $privacySettingData[Privacy::ALLOWED_CONNECTIONS]   ?? [];      // Если в массиве нет такого ключа, то всё ок, допускается ничего вместо пустого массива, чтобы запрос был не таким огромным
            $unallowedConnections = $privacySettingData[Privacy::UNALLOWED_CONNECTIONS] ?? [];
            $allowedLists    = $privacySettingData[Privacy::ALLOWED_LISTS]    ?? [];
            $unallowedLists  = $privacySettingData[Privacy::UNALLOWED_LISTS]  ?? [];
            /*
             * Лучше всего пересечения проверить здесь, потому что здесь это делается перед извлечением connections из БД,
             * если здесь будет дублирование, то выполнение остановится и не будет сделано лишних запросов
             */
            $connectionsIntersections = array_intersect($allowedConnections, $unallowedConnections);
            //print_r($privacySettingData);exit();
            if(count($connectionsIntersections)) {
                $connectionId = $connectionsIntersections[0];
                throw new UnprocessableRequestException(
                    1, "Incorrect structure of '$privacySettingName' param, id '$connectionId'"
                    . " cannot be in 'allowed_connections' and 'unallowed_connections' at same time"
                );
            }
            $connectionsListsIntersections = array_intersect($allowedLists, $unallowedLists);
            if(count($connectionsListsIntersections)) {
                $connectionListId = $connectionsListsIntersections[0];
                throw new UnprocessableRequestException(
                    2, "Incorrect structure of '$privacySettingName' param, id '$connectionListId' "
                    . "cannot be in 'allowed_lists' and 'unallowed_lists' at same time"
                );
            }
            Assertion::isArray($allowedConnections,   "Incorrect structure of '$privacySettingName' param, property 'allowed_connections' should be an array");
            Assertion::isArray($unallowedConnections, "Incorrect structure of '$privacySettingName' param, property 'unallowed_connections' should be an array");
            Assertion::isArray($allowedLists,    "Incorrect structure of '$privacySettingName' param, property 'allowed_lists' should be an array");
            Assertion::isArray($unallowedLists,  "Incorrect structure of '$privacySettingName' param, property 'unallowed_lists' should be an array");
        } catch (\Assert\LazyAssertionException $ex) {
            throw new MalformedRequestException($ex->getMessage());
        }
    }
}