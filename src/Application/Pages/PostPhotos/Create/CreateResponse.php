<?php
declare(strict_types=1);
namespace App\Application\Pages\PostPhotos\Create;

use App\Application\BaseResponse;

class CreateResponse implements BaseResponse {
    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
