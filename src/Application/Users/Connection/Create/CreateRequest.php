<?php
declare(strict_types=1);
namespace App\Application\Users\Connection\Create;

use App\Application\BaseRequest;

class CreateRequest implements BaseRequest {
    public $requesterId;
    public $requesteeId;
    public $subscribe;
    
    function __construct($requesterId, $requesteeId, $subscribe) {
        $this->requesterId = $requesterId;
        $this->requesteeId = $requesteeId;
        $this->subscribe = $subscribe;
    }

}
