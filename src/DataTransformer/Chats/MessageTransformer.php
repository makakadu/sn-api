<?php
declare(strict_types=1);
namespace App\DataTransformer\Chats;

use App\DTO\Chats\MessageDTO;
use App\Domain\Model\Chats\Message;
use App\DataTransformer\Users\Transformer;
use App\Domain\Model\Users\User\User;

class MessageTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transformMultiple(User $requester, array $messages): array {
        $transformed = [];
        foreach($messages as $message) {
            $transformed[] = $this->transform($requester, $message);
        }
        return $transformed;
    }
    
    function transform(User $requester, Message $message): MessageDTO {
        $isReadBy = [];
        foreach($message->chat()->participants() as $participant) {
            if($participant->lastReadMessageId() >= $message->id()) {
                $isReadBy[] = $participant->user()->id();
            }
        }
        
        return new MessageDTO(
            $message->id(),
            $this->creatorToDTO($message->creator()),
            $message->chat()->id(),
            $message->text(),
            $this->creationTimeToTimestamp($message->createdAt()),
            $message->key(),
            $isReadBy
        );
    }
    
}