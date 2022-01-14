<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\PutComment;

use App\Application\BaseResponse;

class PutCommentResponse implements BaseResponse {

    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
