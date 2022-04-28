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
    
    private UserRepository $users;
    
    function __construct(UserRepository $users) {
        $this->users = $users;
    }
    
    function transformMultiple(User $requester, array $chats, int $messagesCount = 10, array $fields = []): array {
        $transformed = [];
        foreach($chats as $chat) {
            $transformed[] = $this->transform($requester, $chat, $messagesCount, $fields);
        }
        return $transformed;
    }
    
    function transform(User $requester, Chat $chat, int $messagesCount = 10, array $fields = []): ChatDTOInterface {
        if(count($fields) > 0) {
            return $this->transformPartial($requester, $chat, $messagesCount, $fields);
        } else {
            return $this->transformFull($requester, $chat, $messagesCount);
        }
    }
    
    function transformFull(User $requester, Chat $chat, int $messagesCount): ChatDTO {
        $messagesCountPlusOne = $messagesCount + 1;
        
        $currentParticipant = $chat->getParticipantByUserId($requester->id());
        
        $messagesDTOs = [];
        $messageCursor = null;
        $messages = [];
        if($messagesCount) {
            $criteria = Criteria::create();
            $criteria
                ->where(Criteria::expr()->eq("deletedForAll", false))
                ->setMaxResults($messagesCountPlusOne)
                ->orderBy(array('id' => Criteria::DESC));
            $messages = $currentParticipant->messages()->matching($criteria)->toArray();
            $messagesArr = [];
            foreach($messages as $message) {
                $messagesArr[] = $message;
            }
            $messages = $messagesArr; // Чтобы индекс был цифрой, а не ID
            if((count($messages) - $messagesCount) === 1) {
                $messageCursor = $messages[count($messages) -1]->id();
                array_pop($messages);
            }
            if(count($messages)) {
                $messagesDTOs = (new MessageTransformer())->transformMultiple($requester, $messages);
            }
        }
        
        $criteria2 = Criteria::create();
        $criteria2->where(Criteria::expr()->eq("deletedForAll", false));
        $criteria2->andWhere(Criteria::expr()->neq("creatorId", $requester->id()));
        if($currentParticipant->lastReadMessageId()) {
            $criteria2->andWhere(Criteria::expr()->gt("id", $currentParticipant->lastReadMessageId()));
        }
        $unreadMessages = $currentParticipant->messages()->matching($criteria2);
        $unreadMessagesCount = $unreadMessages->count();
        
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
        if($chat->type() === 'pair_user_chat') {
            $criteria = Criteria::create();
            $criteria->where(Criteria::expr()->neq("userId", $requester->id()));
            $interlocutorId = $chat->participants()->matching($criteria)->first()->getUserId();
            $participantsDTOs[] = $this->creatorToDTO($requester);
            $interlocutor = $this->users->getById($interlocutorId);
            $participantsDTOs[] = $this->creatorToDTO($interlocutor);
        } else {
            foreach($chat->participants() as $participant) {
                $participantsDTOs[] = $this->creatorToDTO($participant->user());
            }
        }

        $lastReadMessageId = $currentParticipant->lastReadMessageId();
//        echo $currentParticipant->user()->id() . '-----';
        return new ChatDTO(
            $chat->id(),
            $chat->clientId(),
            $participantsDTOs,
            $chat->type(),
            $chat->startedBy(),
            $this->creationTimeToTimestamp($chat->createdAt()),
            $lastMessageDTO,
            $messagesDTOs,
            $messageCursor,
            $lastReadMessageId,
            $unreadMessagesCount
        );
    }
    
    function transformPartial(User $requester, Chat $chat, int $messagesCount, array $fields): PartialChatDTO {
        $dto = new PartialChatDTO($chat->id());
        $dto->clientId = $chat->clientId();
//        echo $messagesCount;exit();
        
        $currentParticipant = null;
        foreach($chat->participants() as $participant) {
            if($participant->user()->equals($requester)) {
                $currentParticipant = $participant;
                break;
            }
        }
        if(\in_array('messages', $fields)) {
            $messagesCountPlusOne = $messagesCount + 1;
            $criteria = Criteria::create();
            $criteria
                ->where(Criteria::expr()->eq("deletedForAll", 0))
                ->setMaxResults($messagesCountPlusOne)
                ->orderBy(array('id' => Criteria::DESC));
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