<?php //
declare(strict_types=1);
namespace App\Application\Chats\TypingMessage;

use App\Application\BaseResponse;

class TypingMessageResponse implements BaseResponse {
    public string $message;
    
    public function __construct(string $message) {
        $this->message = $message;
    }

}
