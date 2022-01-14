<?php
declare(strict_types=1);
namespace App\Application\Users\Connection\Get;

class GetRequest implements \App\Application\BaseRequest {
    public string $requesterId;
    public string $connectionId;
    
    public function __construct(string $requesterId, string $connectionId) {
        $this->requesterId = $requesterId;
        $this->connectionId = $connectionId;
    }

}
