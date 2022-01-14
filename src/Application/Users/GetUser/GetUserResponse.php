<?php
declare(strict_types=1);
namespace App\Application\Users\GetUser;

use App\Domain\Model\Users\User\User;
use App\DTO\Users\ProfileDTO;

class GetUserResponse implements \App\Application\BaseResponse {

    public function __construct(ProfileDTO $dto) {
        foreach((array)$dto as $property => $value) {
            $this->$property = $value;
        }
    }
}
