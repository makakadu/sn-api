<?php
declare(strict_types=1);
namespace App\Application\Users\GetUsers;

use App\Domain\Model\Users\User\User;

class GetUsersResponse implements \App\Application\BaseResponse {
    /** @var array<mixed> $items */
    public array $items;
    public int $allUsersCount;
//    function __construct($requesterId, array $users, int $allUsersCount) {
//
//        $this->items = [];
//        $this->allUsersCount = $allUsersCount;
//        //print_r($users); exit();
//
//        foreach($users as $user) {
//            $this->items[] = [
//                'id' => $user->id(),
//                'name' => $user->firstName() . ' ' . $user->lastName(),
//                'avatar' => '/images/standard/ava.jpg',
//                'isFriend' => $user->isFriend($requesterId),
//                'isPublisher' => $user->isSubscriber($requesterId),
//                'isSubscriber' => \in_array($requesterId, $user->publishers()),
//                'isOfferedFriendship' => \in_array($requesterId, $user->sentFriendshipRequests()),
//                'isReceivedFriendship' => \in_array($requesterId, $user->receivedFriendshipRequests()),
//                'requesterIsBanned' => \in_array($requesterId, $user->receivedFriendshipRequests())
//            ];
//        }
//    }
    /**
     * @param array<User> $users
     */
    function __construct(string $requesterId, array $users, int $allUsersCount) {
        $this->items = [];
        $this->allUsersCount = $allUsersCount;
        
        if($requesterId) {
            
        }

        /** @var User $user */
        foreach($users as $user) {
            $picture = $user->currentPicture();
            if(is_null($picture)) {
                $avatar = null;
            } else {
                $avatar = $picture->small();
            }
            $this->items[] = [
                'id' => $user->id(),
                'isOnline' => false,
                'name' => $user->firstName() . ' ' . $user->lastName(),
                'avatar' => $avatar,
//                'isFriend' => $user->isFriend($requesterId),
//                'isPublisher' => $user->isSubscriber($requesterId),
//                'isSubscriber' => \in_array($requesterId, $user->publishers()),
//                'isOfferedFriendship' => \in_array($requesterId, $user->sentFriendshipRequests()),
//                'isReceivedFriendship' => \in_array($requesterId, $user->receivedFriendshipRequests()),
//                'requesterIsBanned' => \in_array($requesterId, $user->receivedFriendshipRequests())
            ];
        }
    }

}
