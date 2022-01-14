<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\Delete;

use App\Application\BaseResponse;

class DeleteResponse implements BaseResponse {

    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
