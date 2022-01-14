<?php
declare(strict_types=1);
namespace App\Application\Users\Connection\Delete;

use App\Application\BaseRequest;

class DeleteRequest implements BaseRequest {
    public string $requesterId;
    public string $connectionId;
    
    function __construct(string $requesterId, string $connectionId) {
        $this->requesterId = $requesterId;
        $this->connectionId = $connectionId;
    }

}
