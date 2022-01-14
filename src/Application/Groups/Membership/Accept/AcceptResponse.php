<?php
declare(strict_types=1);
namespace App\Application\Groups\Membership\Accept;

use App\Application\BaseResponse;

class AcceptResponse implements BaseResponse {

    public $responseMessage;

    public function __construct(string $responseMessage) {
        $this->responseMessage = $responseMessage;
    }
}
