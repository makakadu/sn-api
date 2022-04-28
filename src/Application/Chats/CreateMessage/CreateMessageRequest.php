<?php
declare(strict_types=1);
namespace App\Application\Chats\CreateMessage;

use App\Application\BaseRequest;

class CreateMessageRequest implements BaseRequest {
    public string $requesterId;
    public string $chatId;
    public string $clientId;
    /** @var mixed $text */
    public $text;
    /** @var mixed $placeId */
    public $placeId;
            
    /**
     * @param mixed $text
     */
    function __construct(string $requesterId, string $chatId, string $clientId, $text, $placeId) {
        $this->requesterId = $requesterId;
        $this->chatId = $chatId;
        $this->clientId = $clientId;
        $this->text = $text;
        $this->placeId = $placeId;
    }
}
