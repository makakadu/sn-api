<?php
declare(strict_types=1);
namespace App\Application\Users;

use App\Application\Exceptions\NotExistException;
use App\Application\ApplicationService;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Subscription\SubscriptionRepository;
use App\Domain\Model\Authorization\SubscriptionsAuth;
use App\Domain\Model\Users\Subscription\Subscription;

abstract class SubscriptionAppService implements ApplicationService {
    use \App\Application\AppServiceTrait;
    
    protected SubscriptionRepository $subscriptions;
    protected SubscriptionsAuth $auth;
    
    function __construct(UserRepository $users, SubscriptionRepository $subscriptions, SubscriptionsAuth $auth) {
        $this->users = $users;
        $this->subscriptions = $subscriptions;
        $this->auth = $auth;
    }
    
    protected function findSubscriptionOrFail(string $subscriptionId): Subscription {
        $subscription = $this->subscriptions->getById($subscriptionId);
        if(!$subscription) {
            throw new NotExistException("Subscription $subscriptionId not found");
        }
        return $subscription;
    }

}