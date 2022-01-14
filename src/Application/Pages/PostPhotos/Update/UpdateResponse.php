<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\PostPhotos\Update;

use App\Application\BaseResponse;

class UpdateResponse implements BaseResponse {

    public string $responseMessage;

    public function __construct(string $responseMessage) {
        $this->responseMessage = $responseMessage;
    }
}
