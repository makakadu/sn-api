<?php
declare(strict_types=1);
namespace App\Application\Groups\Membership\Create;

use App\Application\BaseResponse;

class CreateResponse implements BaseResponse {

    public $responseMessage;

    public function __construct(string $responseMessage) {
        $this->responseMessage = $responseMessage;
    }
}
