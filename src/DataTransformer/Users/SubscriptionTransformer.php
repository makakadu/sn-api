<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;

use App\Domain\Model\Users\Subscription\Subscription;
use App\DTO\Users\SubscriptionDTO;

class SubscriptionTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transform(Subscription $subscription): SubscriptionDTO {
        
        return new SubscriptionDTO(
            $subscription->id(),
            $this->userToSmallDTO($subscription->user()),
            $this->userToSmallDTO($subscription->subscriber()),
            $subscription->pauseDurationInDays(),
            $subscription->pauseStart() ? $this->creationTimeToTimestamp($subscription->pauseStart()) : null,
            $subscription->pauseEnd() ? $this->creationTimeToTimestamp($subscription->pauseEnd()) : null,
        );
    }
    
    function transformMultiple(array $subscriptions): array {
        $subscriptionsDTOs = [];
        foreach($subscriptions as $subscription) {
            $subscriptionsDTOs[] = $this->transform($subscription);
        }
        return $subscriptionsDTOs;
    }
    
}