<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Subscription\Subscription;
use App\Application\Exceptions\ForbiddenException;

class SubscriptionsAuth {
    use AuthorizationTrait;
    
    function failIfCannotSubscribe(User $user1, User $user2): void {
        //$this->failIfInBlacklist($user1, $user2, "Cannot create subscription with user '{$user2->id()} because banned by this user");
    }
    
    function failIfCannotSeeSubscriptionsOf(User $requester, User $user): void {
        if($requester->equals($user)) {
            return;
        }
        $this->failIfInBlacklist($requester, $user, "Banned ");
    }
    
    function failIfCannotUpdate(User $requester, Subscription $subscription): void {
        if($requester->id() !== $subscription->subscriberId()) {
            throw new ForbiddenException(123, "Cannot update subscription {$subscription->id()}. Only subscriber can update");
        }
    }
    
    function failIfCannotDelete(User $requester, Subscription $subscription): void {
        if($requester->id() !== $subscription->subscriberId()) {
            throw new ForbiddenException(123, "Cannot delete subscription {$subscription->id()}. Only subscriber can delete");
        }
    }
}
