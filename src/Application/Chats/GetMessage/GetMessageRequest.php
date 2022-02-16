<?php
declare(strict_types=1);
namespace App\Application\Chats\GetMessage;

use App\Application\BaseRequest;

class GetMessageRequest implements BaseRequest {
    public string $requesterId;
    public string $messageId;
    
    public function __construct(string $requesterId, string $messageId) {
        $this->requesterId = $requesterId;
        $this->messageId = $messageId;
    }

}
