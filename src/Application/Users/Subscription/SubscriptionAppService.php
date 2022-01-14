<?php
declare(strict_types=1);
namespace App\Application\Users\Subscription;

use App\Domain\Model\Users\Subscription\SubscriptionRepository;
use App\Domain\Model\Users\Subscription\Subscription;
use App\Application\Exceptions\NotExistException;
use App\Application\Exceptions\UnprocessableRequestException;

trait SubscriptionAppService {
    
    protected SubscriptionRepository $Subscriptions;

    function findSubscriptionOrFail(string $subscriptionId, bool $asTarget): ?Subscription {
        $subscription = $this->subscriptions->getById($subscriptionId);
        
        $found = true;
        if(!$subscription) {
            $found = false;
        }
        if(!$found && $asTarget) {
            throw new NotExistException("Subscription $subscriptionId not found");
        } elseif(!$found && !$asTarget) {
            throw new UnprocessableRequestException("Subscription $subscriptionId not found");
        }
        return $subscription;
    }

}