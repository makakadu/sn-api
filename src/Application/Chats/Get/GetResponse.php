<?php
declare(strict_types=1);
namespace App\Application\Chats\Get;

use App\Application\BaseResponse;
use App\DTO\Chats\ChatDTO;

class GetResponse implements BaseResponse {
    
    public ChatDTO $chat;

    public function __construct(ChatDTO $chat) {
        $this->chat = $chat;
    }

}