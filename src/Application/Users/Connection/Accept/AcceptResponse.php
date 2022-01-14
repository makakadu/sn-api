<?php
declare(strict_types=1);
namespace App\Application\Users\Connection\Accept;

use App\Application\BaseResponse;

class AcceptResponse implements BaseResponse {

    public string $responseMessage;

    public function __construct(string $responseMessage) {
        $this->responseMessage = $responseMessage;
    }
    
}