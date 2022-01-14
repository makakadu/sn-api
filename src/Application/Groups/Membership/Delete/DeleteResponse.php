<?php
declare(strict_types=1);
namespace App\Application\Groups\Membership\Delete;

use App\Application\BaseResponse;

class DeleteResponse implements BaseResponse {

    public $responseMessage;

    public function __construct(string $responseMessage) {
        $this->responseMessage = $responseMessage;
    }
}
