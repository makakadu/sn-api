<?php
declare(strict_types=1);
namespace App\Application\Users\UpdateUser;

use App\Domain\Model\Users\User\User;
use App\Application\BaseRequest;

class UpdateUserRequest implements BaseRequest {
    public string $requesterId;
    public string $updatingUserId;
    /**
     * @var array<mixed> $payload
     */
    public array $payload;
    
    /**
     * @param array<mixed> $payload
     */
    function __construct(string $requesterId, string $updatingUserId, array $payload) {
        $this->requesterId = $requesterId;
        $this->updatingUserId = $updatingUserId;
        $this->payload = $payload;
    }
}
