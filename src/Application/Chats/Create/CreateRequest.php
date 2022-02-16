<?php
declare(strict_types=1);
namespace App\Application\Chats\Create;

use App\Application\BaseRequest;

class CreateRequest implements BaseRequest {
    public string $requesterId;
    public $participants;
    /** @var mixed $type */
    public $type;
    /** @var mixed $firstMessage */
    public $firstMessage;
    /** @var mixed $frontKey */
    public $frontKey;
            
    /**
     * @param mixed $text
     */
    function __construct(string $requesterId, $participants, $type, $firstMessage, $frontKey) {
        $this->requesterId = $requesterId;
        $this->participants = $participants;
        $this->firstMessage = $firstMessage;
        $this->type = $type;
        $this->frontKey = $frontKey;
    }
}
