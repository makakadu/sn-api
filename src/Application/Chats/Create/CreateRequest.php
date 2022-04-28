<?php
declare(strict_types=1);
namespace App\Application\Chats\Create;

use App\Application\BaseRequest;

class CreateRequest implements BaseRequest {
    public string $requesterId;
    public string $clientId;
    public string $messageClientId;
    public $participants;
    /** @var mixed $type */
    public $type;
    /** @var mixed $firstMessage */
    public $firstMessage;
    /** @var mixed $placeId */
    public $placeId;
            
    /**
     * @param mixed $text
     */
    function __construct(string $requesterId, string $clientId, string $messageClientId, $participants, $type, $firstMessage, $placeId) {
        $this->requesterId = $requesterId;
        $this->clientId = $clientId;
        $this->messageClientId = $messageClientId;
        $this->participants = $participants;
        $this->firstMessage = $firstMessage;
        $this->type = $type;
        $this->placeId = $placeId;
    }
}
