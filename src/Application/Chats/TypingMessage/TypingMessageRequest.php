<?php
declare(strict_types=1);
namespace App\Application\Chats\TypingMessage;

use App\Application\BaseRequest;

class TypingMessageRequest implements BaseRequest {
    public string $requesterId;
    public string $chatId;
            
    public function __construct(string $requesterId, string $chatId) {
        $this->requesterId = $requesterId;
        $this->chatId = $chatId;
    }

}
