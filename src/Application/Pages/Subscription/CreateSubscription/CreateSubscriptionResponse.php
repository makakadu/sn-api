<?php
declare(strict_types=1);
namespace App\Application\Pages\Subscription\CreateSubscription;

use App\Application\BaseResponse;

class CreateSubscriptionResponse implements BaseResponse {

    public $responseMessage;

    public function __construct(string $responseMessage) {
        $this->responseMessage = $responseMessage;
    }
}
