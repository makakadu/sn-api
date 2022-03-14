<?php
declare(strict_types=1);
namespace App\Application\Chats\DeleteHistory;

use App\Application\BaseRequest;

class DeleteHistoryRequest implements BaseRequest {
    public string $requesterId;
    public string $chatId;
    
    public function __construct(string $requesterId, string $chatId) {
        $this->requesterId = $requesterId;
        $this->chatId = $chatId;
    }

}
