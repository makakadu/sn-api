<?php
declare(strict_types=1);
namespace App\Application\Chats\Patch;

use App\Application\BaseResponse;

class PatchResponse implements BaseResponse {

    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
