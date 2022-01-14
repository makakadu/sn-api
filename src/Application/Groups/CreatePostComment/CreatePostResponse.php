<?php //
declare(strict_types=1);
namespace App\Application\Groups\CreatePost;

use App\Application\BaseResponse;

class CreatePostResponse implements BaseResponse {
    public $responseMessage;
    
    function __construct($message) {
        $this->responseMessage = $message;
    }
}
