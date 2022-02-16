<?php
declare(strict_types=1);
namespace App\Application\Chats\DeleteMessage;

use App\Application\BaseRequest;

class DeleteMessageRequest implements BaseRequest {
    public string $requesterId;
    public string $chatId;
    public string $messageId;
    
    public function __construct(string $requesterId, string $chatId, string $messageId) {
        $this->requesterId = $requesterId;
        $this->chatId = $chatId;
        $this->messageId = $messageId;
    }

}
