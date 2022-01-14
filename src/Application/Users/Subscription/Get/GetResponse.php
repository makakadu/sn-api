<?php
declare(strict_types=1);
namespace App\Application\Users\Subscription\Get;

use App\DTO\Users\SubscriptionDTO;

class GetResponse implements \App\Application\BaseResponse {

    public SubscriptionDTO $subscription;
    
    public function __construct(SubscriptionDTO $subscription) {
        $this->subscription = $subscription;
    }

}
