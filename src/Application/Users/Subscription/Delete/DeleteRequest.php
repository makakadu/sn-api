<?php
declare(strict_types=1);
namespace App\Application\Users\Subscription\Delete;

use App\Application\BaseRequest;

class DeleteRequest implements BaseRequest {
    public string $requesterId;
    public string $subscriptionId;
    
    function __construct(string $requesterId, string $subscriptionId) {
        $this->requesterId = $requesterId;
        $this->subscriptionId = $subscriptionId;
    }

}
