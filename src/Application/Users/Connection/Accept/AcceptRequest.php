<?php
declare(strict_types=1);
namespace App\Application\Users\Connection\Accept;

use App\Application\BaseRequest;

class AcceptRequest implements BaseRequest {
    public string $requesterId;
    /** @var mixed $connectionId */
    public $connectionId;
    
    /**
     * @param mixed $connectionId
     */
    public function __construct(string $requesterId, $connectionId) {
        $this->requesterId = $requesterId;
        $this->connectionId = $connectionId;
    }
}
