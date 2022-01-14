<?php
declare(strict_types=1);
namespace App\Application\Users\Auth\GetMe;

use App\Application\BaseRequest;

class GetMeRequest implements BaseRequest {
    public string $requesterId;

    function __construct(string $requesterId) {
        $this->requesterId = $requesterId;
    }

}
