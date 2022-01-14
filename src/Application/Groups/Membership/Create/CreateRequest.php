<?php
declare(strict_types=1);
namespace App\Application\Groups\Membership\Create;

use App\Application\BaseRequest;

class CreateRequest implements BaseRequest {
    public string $requesterId;
    public $userId;
    public string $groupId;

    function __construct(string $requesterId, string $groupId, $userId) {
        $this->requesterId = $requesterId;
        $this->userId = $userId;
        $this->groupId = $groupId;
    }

}
