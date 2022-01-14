<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\DeleteReaction;

use App\Application\BaseResponse;

class DeleteReactionResponse implements BaseResponse {
    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
