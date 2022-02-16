<?php
declare(strict_types=1);
namespace App\Application\Chats\PatchMessage;

use App\Application\BaseResponse;

class PatchMessageResponse implements BaseResponse {

    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
