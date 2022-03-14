<?php
declare(strict_types=1);
namespace App\Domain\Model\Chats;

use Doctrine\Common\Collections\{ArrayCollection, Collection};
use App\Domain\Model\Users\User\User;
use App\Domain\Model\EntityTrait;
use Ulid\Ulid;
use Doctrine\Common\Collections\Criteria;

class Chat {
    use EntityTrait;
    private Collection $participants;
    private Collection $messages;
    private array $events = [];
    private string $uniqueKey;
    private string $type;
    private string $startedBy;
    private ?string $unreadCursor = null;
//    private string $lastActivityId;

    function __construct(User $creator, array $otherParticipants, string $type, ?string $text, string $frontKey) {
        $this->id = (string)Ulid::generate(true);
//        $this->lastActivityId = $this->id;
        $this->startedBy = $creator->id();
        
        if(!in_array($type, ['pair_user_chat', 'group_user_chat'])) {
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
        if($text) {
            $this->createMessage($creator, $text, $frontKey);
        }
    }
    
    function read(User $requester, Message $message): void {
        $currentParticipant = null;
        foreach($this->participants as $participant) {
            if($participant->user()->equals($requester)) {
                $currentParticipant = $participant;
                break;
            }
        }
        if(!$currentParticipant) {
            throw new \App\Application\Exceptions\ForbiddenException(228, 'No rights');
        }
        if(!$this->messages->contains($message)) {
            throw new \App\Domain\Model\DomainExceptionAlt(['message' => "Message {$message->id()} not found in current chat"]);
        }
        if(!$participant->messages()->contains($message)) {
            throw new \App\Domain\Model\DomainExceptionAlt(['message' => "Message {$message->id()} deleted for current user"]);
        }
        $participant->read($message);
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
    
    function createMessage(User $creator, string $text, string $key): Message {
        $canCreate = false;
        $creatorParticipant = null;
        foreach($this->participants as $participant) {
            if($participant->user()->equals($creator)) {
                $creatorParticipant = $participant;
                $canCreate = true;
            }
        }
        if(!$canCreate) {
            throw new \App\Application\Exceptions\ForbiddenException(228, 'No rights');
        }
        $message = new Message($creator, $this, $text, $key);
        $this->messages->add($message);
        foreach($this->participants as $participant) {
            $participant->addMessage($message);
        }
        $creatorParticipant->readMessage($message);
        return $message;
    }
    
    function removeMessageFor(string $messageId, User $user): void {
        $message = $this->messages->get($messageId);
        if(!$message) {
            throw new \App\Application\Exceptions\NotExistException('Message not found');
        }
        $message->removeForUser($user);
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
    
    function removeMessageForAll(User $initiator, string $messageId): void {
        $message = $this->messages->get($messageId);
        if(!$message) {
            throw new \App\Application\Exceptions\NotExistException('Message not found');
        }
        $message->deleteForAll($initiator);

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

    function id(): string {
        return $this->id;
    }
    
    function messages(): Collection {
        return $this->messages;
    }
}
