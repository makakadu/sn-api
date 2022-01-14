<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\Put;

use App\Application\BaseResponse;

class PutResponse implements BaseResponse {

    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
