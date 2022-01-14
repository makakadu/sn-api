<?php
declare(strict_types=1);
namespace App\Application\Users\Subscription\Get;

class GetRequest implements \App\Application\BaseRequest {
    public string $requesterId;
    public string $subId;
    
    public function __construct(string $requesterId, string $subId) {
        $this->requesterId = $requesterId;
        $this->subId = $subId;
    }

}
