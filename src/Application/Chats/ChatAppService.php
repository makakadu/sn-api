<?php
declare(strict_types=1);
namespace App\Application\Chats;

use App\Domain\Model\Users\Post\PostRepository;
use App\Domain\Model\Users\Post\Post;
use App\Application\Exceptions\NotExistException;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Domain\Model\Chats\ChatRepository;
use App\Domain\Model\Chats\MessageRepository;
use App\Domain\Model\Chats\Chat;
use App\Domain\Model\Chats\Message;

trait ChatAppService {
    
    protected ChatRepository $chats;
    protected MessageRepository $messages;

    function findChatOrFail(string $chatId, bool $asTarget): ?Chat {
        $chat = $this->chats->getById($chatId);
        
        $found = true;
        if(!$chat) {
            $found = false;
        }
        if(!$found && $asTarget) {
            throw new NotExistException("Chat $chatId not found");
        } elseif(!$found && !$asTarget) {
            throw new UnprocessableRequestException("Chat $chatId not found");
        }
        return $chat;
    }

    function findMessageOrFail(string $messageId, bool $asTarget): ?Message {
        $message = $this->messages->getById($messageId);
        
        $found = true;
        if(!$message) {
            $found = false;
        }
        if(!$found && $asTarget) {
            throw new NotExistException("Message $messageId not found");
        } elseif(!$found && !$asTarget) {
            throw new UnprocessableRequestException("Message $messageId not found");
        }
        return $message;
    }

}