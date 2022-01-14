<?php
declare(strict_types=1);
namespace App\Application\Users\UpdateUser;

use App\Domain\Model\Identity\User\User;
use App\Application\BaseResponse;

class UpdateUserResponse implements BaseResponse {

    public string $responseMessage;

    public function __construct(string $responseMessage) {
        $this->responseMessage = $responseMessage;
    }
}
