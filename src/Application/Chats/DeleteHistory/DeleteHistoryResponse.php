<?php
declare(strict_types=1);
namespace App\Application\Chats\DeleteHistory;

use App\Application\BaseResponse;
use App\DTO\Chats\MessageDTO;

class DeleteHistoryResponse implements BaseResponse {
    
    public string $message;

    public function __construct(string $message) {
        $this->message = $message;
    }
}