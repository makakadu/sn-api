<?php
declare(strict_types=1);
namespace App\Application\Users\ProfilePicture\CreateComment;

use App\Application\BaseResponse;

class CreateCommentResponse implements BaseResponse {
    //public $dialogue;
    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
