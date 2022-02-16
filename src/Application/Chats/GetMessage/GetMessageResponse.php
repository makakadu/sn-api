<?php
declare(strict_types=1);
namespace App\Application\Chats\GetMessage;

use App\Application\BaseResponse;
use App\DTO\Chats\MessageDTO;

class GetMessageResponse implements BaseResponse {
    
    public MessageDTO $message;

    public function __construct(MessageDTO $messageDTO) {
        $this->message = $messageDTO;
    }
}