<?php
declare(strict_types=1);
namespace App\Application\Users\PatchUser;

class PatchUserRequest implements \App\Application\BaseRequest {
    public string $requesterId;
    public string $userId;
    /** @var mixed $payload */
    public $payload;

    /**
     * @param mixed $payload
     */
    function __construct(string $requesterId, string $userId, $payload) {
        $this->requesterId = $requesterId;
        $this->userId = $userId;
        $this->payload = $payload;
    }

}
