<?php
declare(strict_types=1);
namespace App\Application\Users\PatchUser;

use App\Domain\Model\Users\User\User;
use App\DTO\Users\ProfileDTO;

class PatchUserResponse implements \App\Application\BaseResponse {

    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
