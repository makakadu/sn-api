<?php
declare(strict_types=1);
namespace App\Application\Pages\Subscription\CreateSubscription;

use App\Application\BaseRequest;

class CreateSubscriptionRequest implements BaseRequest {
    public $requesterId;
    public $pageId;
    
    public function __construct($requesterId, $pageId) {
        $this->requesterId = $requesterId;
        $this->pageId = $pageId;
    }
}
