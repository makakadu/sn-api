<?php
declare(strict_types=1);
namespace App\Application\Users\ProfilePicture\Update;

use App\Application\BaseResponse;

class UpdateResponse implements BaseResponse {

    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
