<?php
declare(strict_types=1);
namespace App\Application\Pages\Subscription\UpdateSubscription;

use App\Application\BaseResponse;

class CreateConnectionResponse implements BaseResponse {

    public $responseMessage;

    public function __construct(string $responseMessage) {
        $this->responseMessage = $responseMessage;
    }
}
