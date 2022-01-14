<?php

declare(strict_types=1);

namespace App\Domain\Model\Dialogue;

use Doctrine\Common\Collections\{ArrayCollection, Collection};
use App\Domain\Model\Identity\User\User;
use App\Domain\Model\Identity\User\UserId;

// Если пользователи забанили друг друга, то это не влияет на то, могут ли они слать сообщения в чате

class Conversation extends Chat {
    private UserId $creatorId;

    function __construct(UserId $creatorId, array $participants) {
        parent::__construct($participants);
        $this->creatorId = $creatorId;
    }
    
    function creatorId(): UserId {
        return $this->creatorId;
    }
    
    function createMessage(UserId $senderId, string $text): Message {
        $this->failIfNotAParticipant($senderId); 
        $sender = $this->users->get($senderId);
        $message = new Message($sender, $text);
        
        foreach($this->messagesHistories as $history) {
            $history->addMessage($message); // Отличный пример aggregate, множество сущностей меняются, но все они находится внутри aggregate root и изменения сохранятся вместе с сохранением root.
        }
        $this->removedForFirstUser = $this->removedOnSecondUser = false;
        return $message;
    }
    
    function addParticipant(User $participant): void {
        $this->participants->add($participant);
    }
    
    function removeParticipant(int $participantId): void {
        $this->participants->remove($participantId);
        $participantHistory = $this->getParticipantHistory($participantId);
        $this->messagesHistories->removeElement($participantHistory);
    }
}
