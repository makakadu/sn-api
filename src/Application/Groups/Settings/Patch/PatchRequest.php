<?php
declare(strict_types=1);
namespace App\Application\Groups\Settings\Patch;

use App\Application\BaseRequest;

class PatchRequest implements BaseRequest {
    public string $requesterId;
    public string $groupId;
    /** @var mixed $payload */
    public $payload;
    
    /** @param mixed $payload */
    function __construct(string $requesterId, string $groupId, $payload) {
        $this->requesterId = $requesterId;
        $this->groupId = $groupId;
        $this->payload = $payload;
    }
}
