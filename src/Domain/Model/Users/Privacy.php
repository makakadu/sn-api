<?php
declare(strict_types=1);
namespace App\Domain\Model\Users;

use App\Domain\Model\Users\AccessLevels as AL;
use Assert\Assertion;
use App\Domain\Model\DomainException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Users\User\User;

abstract class Privacy {
    const ALLOWED_CONNECTIONS = 'allowed_connections';
    const UNALLOWED_CONNECTIONS = 'unallowed_connections';
    const ALLOWED_LISTS = 'allowed_lists';
    const UNALLOWED_LISTS = 'unallowed_lists';
    const ACCESS_LEVEL = 'access_level';
    const REDUCE_IF_PROFILE_CLOSED = "reduceIfProfileClosed";
    
    protected int $id;
    
    protected User $user;

    protected function failIfOutOfRange(int $newAccessLevel): void {
        if($newAccessLevel < AL::NOBODY || $newAccessLevel > AL::EVERYONE) {
            throw new \OutOfRangeException("$newAccessLevel acces level does not exist");
        }
    }
    
//    protected function findFriendshipsOfUser(ArrayCollection $friendships, User $user): ArrayCollection {
//        $criteria = Criteria::create()
//            ->where(Criteria::expr()->eq("user1_id", $user->id()))
//            ->andWhere(Criteria::expr()->eq("user2_id", $user->id()));
//        return $friendships->matching($criteria);
//    }
    
//    protected function findListsOfUser(ArrayCollection $lists, User $user): ArrayCollection {
//        $criteria = Criteria::create()
//            ->where(Criteria::expr()->eq("creator", $user->id()));
//        return $lists->matching($criteria);
//    }
//    
//    protected function prepareUpdatedSetting(array $data): array
//    {
//        $lists = $data['lists'];
//        
//        $prepared[self::ACCESS_LEVEL] = $data[self::ACCESS_LEVEL];
//        // если в $lists не достаёт какого-то ключа, то заменяем его пустым, подразумевая то, что клиент не добавил этот ключ за ненадобностью. Зачем загрязнять тело запроса пустым массивом? Особенно такой подход хорош, когда нужно изменить много настроек за один раз
//        $allowedFriends = $prepared[self::ALLOWED_CONNECTIONS] = $lists[self::ALLOWED_CONNECTIONS] ?? [];
//        $unallowedFriends = $prepared[self::UNALLOWED_CONNECTIONS] = $lists[self::UNALLOWED_CONNECTIONS] ?? [];
//        $allowedGroups = $prepared[self::ALLOWED_LISTS] = $lists[self::ALLOWED_LISTS] ?? [];
//        $unallowedGroups = $prepared[self::UNALLOWED_LISTS] = $lists[self::UNALLOWED_LISTS] ?? [];
//        
//        $this->validateLists($data['accessLevel'], $allowedFriends, $unallowedFriends, $allowedGroups, $unallowedGroups); // validateLists принимает 4 массива, если это не массивы, то в нём будет выброшена ошибка
//
//        return $prepared;
//    }
    
    protected function throwAccessLevelIsNotAllowedException(int $accessLevel, string $settingName): void {
        throw new \RangeException("Access level {$accessLevel} is not appropriate for '$settingName' setting");
    }
    
   // если здесь будет исключение, то это логическая ошибка
//    protected function validateLists(
//        int $newAccessLevel,
//        array $allowedFriends, array $unallowedFriends,
//        array $allowedGroups, array $unallowedGroups
//    ): void {
//        try {
//            // Во-первых в списках будут использоваться не объекты класса User, а объекты класса Friendship
//            // Чтобы получить Friendship нужен репозиторий, либо можно сделать так, чтобы User содержал коллекцию из объектов Friendship
//            // Но мне кажется, что лучше получить коллекцию указанных friendship и передать их в метод User::changePrivacySetting()
//            // И здесь проверить является ли пользователь участником этих friendships
//            // 
//            $this->failIfNonexistentFriendInList($allowedFriends, $unallowedFriends);
//            $this->failIfNonexistentGroupInList($allowedGroups, $unallowedGroups);
//        } catch (\Assert\InvalidArgumentException $ex) {
//            throw new DomainException($ex->getMessage());
//        }
//        
//        $this->failIfListsAreNotConsistentWithAccessLevel(
//            $newAccessLevel, $allowedFriends, $unallowedFriends, $allowedGroups, $unallowedGroups
//        );
//    }

//    function failIfListsAreNotConsistentWithAccessLevel(
//        int $accessLevel,
//        array $allowedFriends, array $unallowedFriends,
//        array $allowedGroups, array $unallowedGroups
//    ): void {
//        
//        if($accessLevel === AL::NOBODY) {
//            Assertion::allTrue([
//                empty($allowedFriends), empty($unallowedFriends), empty($allowedGroups), empty($unallowedGroups)
//            ], "If access level is ".AL::NOBODY." then all lists should be empty");
//            
//        } elseif($accessLevel >= AL::CONNECTIONS && $accessLevel <= AL::EVERYONE) {
//            $errorMessage = "If access level is ".AL::CONNECTIONS.", ".AL::CONNECTIONS_OF_CONNECTIONS." or ".AL::EVERYONE." lists with allowed friends and allowed groups of friends should be empty";
//            Assertion::allTrue([empty($allowedFriends), empty($allowedGroups)], $errorMessage);
//            
//        } elseif($accessLevel === AL::SOME_CONNECTIONS_AND_LISTS) {
//            $errorMessage = "If access level is ".AL::SOME_CONNECTIONS_AND_LISTS." at least one allowed friend or 'group of friends' should be specified";
//            Assertion::true((count($allowedFriends) + count($allowedGroups) > 0), $errorMessage);
//            $errorMessage2 = "If access level is ".AL::SOME_CONNECTIONS_AND_LISTS." lists with unallowed friends and unallowed groups of friends should be empty";
//            Assertion::allTrue([empty($unallowedFriends), empty($unallowedGroups)], $errorMessage2);
//        }
//    }

//    protected function failIfNonexistentFriendInList(array $allowedFriends, array $unallowedFriends): void {
//        foreach($allowedFriends as $id) {
//            $errorMessage = "$id is not id of friend. Only friends can be added to list of users who have access";
//            Assertion::inArray($id, $this->user->friendsIds(), $errorMessage);
//        }
//        foreach($unallowedFriends as $id) {
//            $errorMessage = "$id is not id of friend. Only friends can be added to list of users who don't have access";
//            Assertion::inArray($id, $this->user->friendsIds(), $errorMessage);
//        }
//    }
//    
//    protected function failIfNonexistentGroupInList(array $allowedGroups, array $unallowedGroups): void {
//        foreach($allowedGroups as $id) {
//            $errorMessage = "Cannot add nonexistent group of friends ($id) to list of allowed groups of friends";
//            Assertion::inArray($id, $this->user->friendsLists(), $errorMessage);
//        }
//        foreach($unallowedGroups as $id) {
//            $errorMessage = "Cannot add nonexistent group of friends ($id) to list of unallowed groups of friends";
//            Assertion::inArray($id, $this->user->friendsLists(), $errorMessage);
//        }
//    }
}
