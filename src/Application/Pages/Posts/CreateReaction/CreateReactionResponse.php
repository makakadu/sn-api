<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\CreateReaction;

use App\Application\BaseResponse;

class CreateReactionResponse implements BaseResponse {
    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
