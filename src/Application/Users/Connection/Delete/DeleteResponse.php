<?php
declare(strict_types=1);
namespace App\Application\Users\Connection\Delete;

use App\Application\BaseResponse;

class DeleteResponse implements BaseResponse {

    public string $responseMessage;

    public function __construct(string $responseMessage) {
        $this->responseMessage = $responseMessage;
    }
}
