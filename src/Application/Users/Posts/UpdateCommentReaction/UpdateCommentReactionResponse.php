<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\UpdateCommentReaction;

class UpdateCommentReactionResponse implements \App\Application\BaseResponse {

    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
