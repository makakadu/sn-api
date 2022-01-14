<?php
declare(strict_types=1);
namespace App\Application\Users\GetUser;

class GetUserRequest implements \App\Application\BaseRequest {
    public ?string $requesterId;
    /** @var mixed $requestedUserId */
    public $requestedUserId;

    /**
     * @param mixed $requestedUserId
     */
    function __construct(?string $requesterId, $requestedUserId) {
        $this->requesterId = $requesterId;
        $this->requestedUserId = $requestedUserId;
    }

}
