<?php
declare(strict_types=1);
namespace App\Application\Users\ConnectionsLists\Create;

use App\Application\BaseRequest;

class CreateRequest implements BaseRequest {
    public string $requesterId;
    /** @var mixed $name */
    public $name;
    /** @var mixed $connections */
    public $connections;
    
    /**
     * @param mixed $name
     * @param mixed $connections
     */
    function __construct(string $requesterId, $name, $connections) {
        $this->requesterId = $requesterId;
        $this->name = $name;
        $this->connections = $connections;
    }

}
