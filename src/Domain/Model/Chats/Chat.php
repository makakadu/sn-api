<?php
declare(strict_types=1);
namespace App\Domain\Model\Chats;

use Doctrine\Common\Collections\{ArrayCollection, Collection};
use App\Domain\Model\Users\User\User;
use App\Domain\Model\EntityTrait;
use Ulid\Ulid;
use Doctrine\Common\Collections\Criteria;
use App\Domain\Model\Chats\Actions\CreateMessageAction;
use App\Domain\Model\Chats\Actions\CreateChatAction;

class Chat {
    const PAIR_CHAT = 'pair_user_chat';
    const GROUP_CHAT = 'group_user_chat';
    
    use EntityTrait;
    private Collection $participants;
    private Collection $messages;
    private Collection $actions;
    private array $events = [];
    private string $uniqueKey;
    private string $type;
    private string $startedBy;
    private ?string $unreadCursor = null;
    private string $clientId;
//    private string $lastActivityId;

    function __construct(string $clientId, User $creator, array $otherParticipants, string $type, ?string $text, string $messageClientId) {
        $this->id = (string)Ulid::generate(true);
//        $this->lastActivityId = $this->id;
        $this->clientId = $clientId;
        $this->startedBy = $creator->id();
        
        if(!in_array($type, [self::PAIR_CHAT, self::GROUP_CHAT])) {
            throw new \InvalidArgumentException('Invalid chat type');
        }
        
        $this->participants = new ArrayCollection();
        $this->participants->add(new Participant($creator, $this));
        foreach($otherParticipants as $participant) {
            $this->participants->add(new Participant($participant, $this));
        }
        if($this->participants->count() < 2) {
            throw new \InvalidArgumentException('At least two participants should be in chat');
        }
        if($type === 'pair_user_chat' && $this->participants->count() > 2) {
            throw new \InvalidArgumentException('Only 2 participants can be in PAIR chat');
        }
        $this->messages = new ArrayCollection();
        $this->actions = new ArrayCollection();
        
        $this->createdAt = new \DateTime('now');
        $this->type = $type;
        
        if($type === 'pair_user_chat') { // Если чат парный, то состав участников должен быть уникальный, поэтому делаем уникальный ключ, при повторенни которого будет ошибка
            $user1 = $this->participants[0]->user();
            $user2 = $this->participants[1]->user();
            
            $this->uniqueKey = $user1->id() < $user2->id()
                ? ($user1->id() . $user2->id())
                : ($user2->id() . $user1->id());
        } elseif($type === 'group_user_chat') {
            $this->uniqueKey = (string)Ulid::generate(true);
        }
        $firstMessage = null;
        if($text) {
            $firstMessage = $this->createMessage($creator, $text, $messageClientId, true);
        }
        $this->actions->add(new CreateChatAction($this, $creator, $firstMessage));
    }
    
    function getLastMessageOfUser(User $user): ?Message {
        $currentParticipant = $this->getParticipantByUserId($user->id());
        if(!$currentParticipant) {
            return null;
        }
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq("deletedForAll", false));
        $criteria->orderBy(array('id' => Criteria::DESC));
        $criteria->setMaxResults(1);
        return $currentParticipant->messages()->matching($criteria)->first();
    }
    
    function getUniqueKey(): string {
        return $this->uniqueKey;
    }
    
    function addAction(Action $action): void  {
        $this->actions->add($action);
    }
    
//    function getActionsForUser(User $user, ?string $cursor, ?int $count): Collection {
//        $cursorDate = new \DateTime();
//        $cursorDate->setTimestamp($cursor);
//        
//        $criteria = Criteria::create();
//        $criteria->where(Criteria::expr()->eq("type", 1));
//        $criteria->orWhere(Criteria::expr()->eq("type", 2));
//        $criteria->where(Criteria::expr()->eq("type", 3));
//        if($cursor) {
//            $criteria->andWhere(Criteria::expr()->gte('createdAt', $cursorDate));
//        }
//        if($count) {
//            $criteria->setMaxResults($count);
//        }
// 
//
//        $actions = $this->actions->matching($criteria);
//    }
    
    function clientId(): string {
        return $this->clientId;
    }
    
    function isParticipant(User $user): bool {
        foreach($this->participants as $participant) {
            if($participant->userId() === $user->id()) {
                return true;
            }
        }
        return false;
    }
    
    function getParticipantByUserId(string $userId): ?Participant {
        foreach($this->participants as $participant) {
            if($participant->userId() === $userId) {
                return $participant;
            }
        }
        return null;
    }
    
    function getLastAction(): ?Action {
        $lastMessage = $this->actions->last();
        return $lastMessage ? $lastMessage : null;
    }
    
    function read(User $requester, Message $message): void {
        $currentParticipant = $this->getParticipantByUserId($requester->id());
        
        if(!$currentParticipant) {
            throw new \App\Application\Exceptions\ForbiddenException(228, 'No rights');
        }
        if(!$this->messages->contains($message)) {
            throw new \App\Domain\Model\DomainExceptionAlt(['message' => "Message {$message->id()} not found in current chat"]);
        }
        if(!$currentParticipant->messages()->contains($message)) {
            throw new \App\Domain\Model\DomainExceptionAlt(['message' => "Message {$message->id()} deleted for current user"]);
        }
        $currentParticipant->read($message);
        
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq("deletedForAll", false));
        $criteria->andWhere(Criteria::expr()->gt("id", $currentParticipant->lastReadMessageId()));
        $unreadMessagesCount = $currentParticipant->messages()->matching($criteria)->count();
        
        $this->actions->add(new Actions\ReadMessageAction($this, $requester, $message->id(), $message->clientId(), $message->creatorId(), $unreadMessagesCount));
    }
    
    function type(): string {
        return $this->type;
    }


    function startedBy(): string {
        return $this->startedBy;
    }
    
    function participants(): Collection {
        return $this->participants;
    }
    
    function addParticipant(User $participant): void {
        $this->participants->add($participant);
    }
    
    function removeParticipant(string $participantId): void {
        $this->participants->remove($participantId);
    }
    
    function createMessage(User $creator, string $text, string $clientId, bool $isNewChat = false): Message {
        $creatorParticipant = $this->getParticipantByUserId($creator->id());
        if(!$creatorParticipant) {
            throw new \App\Application\Exceptions\ForbiddenException(228, 'No rights');
        }
        $message = new Message($clientId, $creator, $this, $text);
        $this->messages->add($message);
        foreach($this->participants as $participant) {
            $participant->addMessage($message);
        }
        $creatorParticipant->readMessage($message);
        if(!$isNewChat) {
            $this->actions->add(new CreateMessageAction($this, $creator, $message));
        }
        return $message;
    }
    
    function removeMessageFor(string $messageId, User $user): void {
        $participant = $this->getParticipantByUserId($user->id());
        if(!$participant) {
            throw new \App\Application\Exceptions\ForbiddenException(228, 'No rights');
        }
        $message = $participant->messages()->get($messageId);
        if(!$message) {
            throw new \App\Application\Exceptions\NotExistException('Message not found');
        }
        $participant->messages()->remove($messageId);
//        $lastReadMessageId = $participant->lastReadMessageId();
//        if($lastReadMessageId < $message->id()) {
//            $participant->readMessage($message);
//        }
        $this->actions->add(new Actions\DeleteMessageAction($this, $user, $message->id(), $message->clientId(), $message->creator()->id()));
//
//        $criteria = Criteria::create();
//        $criteria
//            ->where(Criteria::expr()->eq("deletedForAll", 0))
//            ->andWhere(Criteria::expr()->notIn("deletedFor", [$user->id()]))
//            ->setMaxResults(1)
//            ->orderBy(array('id' => Criteria::DESC));
//        
//        $lastActiveMessage = $this->messages()->matching($criteria)->first();
//        if($lastActiveMessage) {
//            $this->lastActivityId = $lastActiveMessage->id();
//        } else {
//            $this->lastActivityId = $this->id(); // Если нет ни одного не удалённого сообщения, то последней активностью, по которой будет происходить сортировка,
//            // будет - создание чата
//        }
    }
    
//    function removeMessageForAll(User $initiator, string $messageId): void {
//        $message = $this->messages->get($messageId);
//        if(!$message) {
//            throw new \App\Application\Exceptions\NotExistException('Message not found');
//        }
//        $message->deleteForAll($initiator);
//        $this->actions->add(new Action($this, $initiator, Action::DELETE_MESSAGE_FOR_ALL, $message->id(), $message->clientId(), $message->creator()->id()));
//
////        $criteria = Criteria::create();
////        $criteria
////            ->where(Criteria::expr()->eq("deletedForAll", 0))
////            ->andWhere(Criteria::expr()->notIn("deletedFor", [$user->id()]))
////            ->setMaxResults(1)
////            ->orderBy(array('id' => Criteria::DESC));
////        
////        $lastActiveMessage = $this->messages()->matching($criteria)->first();
////        if($lastActiveMessage) {
////            $this->lastActivityId = $lastActiveMessage->id();
////        } else {
////            $this->lastActivityId = $this->id(); // Если нет ни одного не удалённого сообщения, то последней активностью, по которой будет происходить сортировка,
////            // будет - создание чата
////        }
//    }

    function id(): string {
        return $this->id;
    }
    
    function messages(): Collection {
        return $this->messages;
    }
}
