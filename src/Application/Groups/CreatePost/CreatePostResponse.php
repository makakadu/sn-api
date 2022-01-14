<?php //
declare(strict_types=1);
namespace App\Application\Groups\CreatePost;

use App\Application\BaseResponse;

class CreatePostResponse implements BaseResponse {
    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
