<?php
declare(strict_types=1);
namespace App\Application\Users\ConnectionsLists\Update;

use App\Application\BaseRequest;

class UpdateRequest implements BaseRequest {
    public string $requesterId;
    public string $listId;
    /** @var mixed $payload */
    public $payload;

    /** @param mixed $payload */
    function __construct(string $requesterId, string $listId, $payload) {
        $this->requesterId = $requesterId;
        $this->listId = $listId;
        $this->payload = $payload;
    }

}
