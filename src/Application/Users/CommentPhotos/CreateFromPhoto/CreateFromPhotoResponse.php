<?php
declare(strict_types=1);
namespace App\Application\Users\CommentPhotos\CreateFromPhoto;

use App\Application\BaseResponse;

class CreateFromPhotoResponse implements BaseResponse {
    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
