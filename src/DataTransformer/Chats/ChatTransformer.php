<?php
declare(strict_types=1);
namespace App\DataTransformer\Chats;

use App\DTO\Chats\ChatDTO;
use App\Domain\Model\Chats\Chat;
use App\DTO\Chats\MessageDTO;
use App\DataTransformer\Users\Transformer;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Users\User\User;
use App\DataTransformer\Chats\MessageTransformer;
use App\DTO\Chats\ChatDTOInterface;
use App\DTO\Chats\PartialChatDTO;
use App\Domain\Model\Users\User\UserRepository;

class ChatTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transformMultiple(User $requester, array $chats, ?int $messagesCount, array $fields = []): array {
        $transformed = [];
        foreach($chats as $chat) {
            $transformed[] = $this->transform($requester, $chat, $messagesCount, $fields);
        }
        return $transformed;
    }
    
    function transform(
        User $requester,
        Chat $chat, 
        ?int $messagesCount, 
        array $fields = [], 
        ?string $messagesOrder = null
    ): ChatDTOInterface {
        if(count($fields) > 0) {
            return $this->transformPartial($requester, $chat, $messagesCount, $fields, $messagesOrder);
        } else {
            return $this->transformFull($requester, $chat, $messagesCount, $messagesOrder);
        }
    }
    
    function transformFull(User $requester, Chat $chat, ?int $messagesCount, ?string $messagesOrder): ChatDTO {
        $currentParticipant = $chat->getParticipantByUserId($requester->id());
        
        $messagesDTOs = [];
        $messageCursor = null;
        $messages = [];

        $lastReadMessageId = $currentParticipant->lastReadMessageId();
        
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq("deletedForAll", false));
        $criteria->andWhere(Criteria::expr()->neq("creatorId", $requester->id()));
        
        if($lastReadMessageId) { // Если нет $lastReadMessageId то все сообщения являются НЕпрочитанными
            $criteria->andWhere(Criteria::expr()->gt("id", $lastReadMessageId));
        }
        $unreadMessages = $currentParticipant->messages()->matching($criteria)->toArray();
        $unreadMessagesCount = count($unreadMessages);
            
        $cursorBefore = null;
        $cursorAfter = null;
        if($messagesCount === null && !$messagesOrder) {
            if($unreadMessagesCount >= 40) {
                $readMessagesCount = 0;
                $readMessages = [];
                if($lastReadMessageId) { // Если нет $lastReadMessageId, то значит нет прочитанных сообщений
                    $criteria = Criteria::create();
                    $criteria->where(Criteria::expr()->eq("deletedForAll", false));
                    $criteria->andWhere(Criteria::expr()->lte("id", $lastReadMessageId));
                    $criteria->setMaxResults(11);
                    $criteria->orderBy(array('id' => 'DESC'));
                    $readMessages = $currentParticipant->messages()->matching($criteria)->toArray();
                    $readMessagesCount = count($readMessages);
                }
                // Если непрочитанных 40 или больше, то возвращаем 40 unread и 10 read, если read меньше 10, то прибавляем к 40 unread 10 - read.count
                $neededReadMessagesCount = $readMessagesCount > 10
                    ? 10 : $readMessagesCount; // Это количество read сообщений, которые нужно вернуть, 10 - это максимум
                $neededUnreadMessagesCount = 40 + (10 - $neededReadMessagesCount);
                // Если прочитанных 9, то непрочитанных будет 41, если 8, то 42 и так далее.
                
                $unreadMessagesArrWithNumberIndexes = [];
                foreach($unreadMessages as $unreadMessage) {
                    $unreadMessagesArrWithNumberIndexes[] = $unreadMessage;
                }
                $unreadMessages = $unreadMessagesArrWithNumberIndexes;
                
                $readMessagesArrWithNumberIndexes = [];
                foreach($readMessages as $readMessage) {
                    $readMessagesArrWithNumberIndexes[] = $readMessage;
                }
                $readMessages = $readMessagesArrWithNumberIndexes;
                
                if($unreadMessagesCount > $neededUnreadMessagesCount) { // Если unread больше, чем нужно, то обрезаем массив с unread и получаем курсор
                    $cursorBefore = $unreadMessages[$neededUnreadMessagesCount]->id(); // Если их столько, сколько нужно или меньше, то ничего не делаем.
                    $unreadMessages = array_slice($unreadMessages, 0, $neededUnreadMessagesCount); // $unreadMessages идут в ASC порядке, поэтому самые новые в конце
                }
                if($readMessagesCount > $neededReadMessagesCount) {
                    $cursorAfter = $readMessages[$neededReadMessagesCount]->id();
                    $readMessages = array_slice($readMessages, 0, $neededReadMessagesCount); // $readMessages идут в DESC порядке, поэтому самые старые в конце
                }
                $messages = array_merge(array_reverse($unreadMessages), $readMessages);
            } else { // Если непрочитанных сообщений меньше, чем 40, то просто извлекаем все сообщения подряд начиная с последнего
                $criteria = Criteria::create();
                $criteria->where(Criteria::expr()->eq("deletedForAll", false));
                $criteria->setMaxResults(51);
                $criteria->orderBy(array('id' => 'DESC'));
                $result = $currentParticipant->messages()->matching($criteria)->toArray();
//                echo $result[0]->text();exit();
                if(count($result) > 50) {
                    $cursorAfter = $result[50]->id();
                    $result = array_slice($result, 0, 50);
                }
                $messages = $result;
            }

            $messagesDTOs = (new MessageTransformer())->transformMultiple($requester, $messages);
        }
        elseif($messagesCount !== null) {
            $order = $messagesOrder ? $messagesOrder : 'DESC';
            
            $criteria = Criteria::create();
            $criteria->where(Criteria::expr()->eq("deletedForAll", false));
            $messagesCountPlusOne = $messagesCount + 1;
            $criteria->setMaxResults($messagesCountPlusOne);
            $criteria->orderBy(array('id' => $order));
            
            $result = $currentParticipant->messages()->matching($criteria)->toArray();
            $messagesArr = [];
            foreach($result as $message) {
                $messagesArr[] = $message;
            }
            $messages = $messagesArr; // Чтобы индекс был цифрой, а не ID
            
            if((count($messages) - $messagesCount) === 1) {
                $cursorAfter = $messages[count($messages) -1]->id();
                array_pop($messages);
//                if($order === 'ASC') {
//                    $cursorAfter = $messages[count($messages) -1]->id();
//                    array_shift($messages);
//                } else {
//                    $cursorBefore = $messages[count($messages) -1]->id();
//                    array_pop($messages); 
//                }
            }
            if(count($messages)) {
                $messagesDTOs = (new MessageTransformer())->transformMultiple($requester, $messages);
            }
        }
        
        $criteria3 = Criteria::create();
        $criteria3->where(Criteria::expr()->eq("deletedForAll", false));
        $criteria3->orderBy(array('id' => Criteria::DESC));
        $criteria3->setMaxResults(1);
        
        $lastMessage = $currentParticipant->messages()->matching($criteria3)->first();
        $lastMessageDTO = $lastMessage
            ? (new MessageTransformer())->transform($requester, $lastMessage) : null;
        
        if($lastMessage && !$messagesCount) {
            $messageCursor = $lastMessage->id();
        }
        
        $participantsDTOs = [];
        foreach($chat->participants() as $participant) {
            $participantsDTOs[] = $this->creatorToDTO($participant->user());
        }

        return new ChatDTO(
            $chat->id(),
            $chat->clientId(),
            $participantsDTOs,
            $chat->type(),
            $chat->startedBy(),
            $this->creationTimeToTimestamp($chat->createdAt()),
            $lastMessageDTO,
            $messagesDTOs,
            $cursorBefore,
            $cursorAfter,
            $lastReadMessageId,
            $unreadMessagesCount
        );
    }
    
    function transformPartial(User $requester, Chat $chat, int $messagesCount, array $fields, string $messagesOrder = "ASC", ?string $messagesCursor = null): PartialChatDTO {
        $dto = new PartialChatDTO($chat->id());
        $dto->clientId = $chat->clientId();
        
        $currentParticipant = null;
        foreach($chat->participants() as $participant) {
            if($participant->user()->equals($requester)) {
                $currentParticipant = $participant;
                break;
            }
        }
        if(\in_array('messages', $fields) && $messagesCount !== 0) {
            
            $criteria = Criteria::create();
            $criteria->where(Criteria::expr()->eq("deletedForAll", 0));
            if($messagesCursor) {
                $criteria->andWhere(Criteria::expr()->gte("id", $messagesCursor));
            }
            if($messagesCount) {
                $messagesCountPlusOne = $messagesCount + 1;
                $criteria->setMaxResults($messagesCountPlusOne);
            }
            $criteria->orderBy(array('id' => $messagesOrder));

            $messages = $currentParticipant->messages()->matching($criteria)->toArray();
            $messageCursor = null;
            if((count($messages) - $messagesCount) === 1) {
                $messageCursor = $messages[count($messages) -1]->id();
                array_pop($messages);
            }
            $messagesDTOs = (new MessageTransformer())->transformMultiple($requester, $messages);
            $dto->messages = $messagesDTOs;
            if(\in_array('messageCursor', $fields)) {
                $dto->messageCursor = $messageCursor;
            }
        }
        if(\in_array('unreadMessagesCount', $fields)) {
            $criteria2 = Criteria::create();
            $criteria2->where(Criteria::expr()->eq("deletedForAll", 0));
            $criteria2->andWhere(Criteria::expr()->neq("creatorId", $requester->id()));
            if($currentParticipant->lastReadMessageId()) {
                $criteria2->andWhere(Criteria::expr()->gt("id", $currentParticipant->lastReadMessageId()));
            }
            $unreadMessagesCount = $currentParticipant->messages()->matching($criteria2)->count();
            $dto->unreadMessagesCount = $unreadMessagesCount;
        }
        if(\in_array('lastMessage', $fields)) {
            $criteria3 = Criteria::create();
            $criteria3->where(Criteria::expr()->eq("deletedForAll", 0));
            $criteria3->orderBy(array('id' => Criteria::DESC));
            $criteria3->setMaxResults(1);

            $lastMessage = $currentParticipant->messages()->matching($criteria3)->first();
            $lastMessageDTO = $lastMessage ? (new MessageTransformer())->transform($requester, $lastMessage) : null;
            $dto->lastMessage = $lastMessageDTO;
        }
        if(\in_array('participants', $fields)) {
            $participantsDTOs = [];
            foreach($chat->participants() as $participant) {
                $participantsDTOs[] = $this->creatorToDTO($participant->user());
            }
            $dto->participants = $participantsDTOs;
        }
        if(\in_array('lastReadMessageId', $fields)) {
            $dto->lastReadMessageId = $currentParticipant->lastReadMessageId();
        }
        if(\in_array('type', $fields)) {
            $dto->type = $chat->type();
        }
        if(\in_array('startedBy', $fields)) {
            $dto->startedBy = $chat->startedBy();
        }
        if(\in_array('createdAt', $fields)) {
            $dto->createdAt = $this->creationTimeToTimestamp($chat->createdAt());
        }
        
        return $dto;
    }
    
}