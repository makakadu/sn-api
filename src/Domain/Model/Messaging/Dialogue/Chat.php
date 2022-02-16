<?php

declare(strict_types=1);

namespace App\Domain\Model\Dialogue;

use Doctrine\Common\Collections\{ArrayCollection, Collection};
use App\Domain\Model\Identity\User\User;
use App\Domain\Model\Identity\User\UserId;
// Если пользователи забанили друг друга, то это не влияет на то, могут ли они слать сообщения в чате

abstract class Chat {
    public $version;
    protected int $id;
    protected Collection $participants, $messagesHistories, $allMessages;
    protected \DateTime $creationDate;
    protected ?\DateTime $lastMessageDate = null;
    protected array $events = [];

    function __construct(array $participants) {
        $this->id = uniqid();
        $this->participants = new ArrayCollection();
        $this->messagesHistories = new ArrayCollection();
        $this->allMessages = new ArrayCollection();
        foreach($participants as $participant) {
            $this->participants[$participant->id()] = $participant;
            $this->messagesHistories->add(new MessagesHistory($this, $participant->id()));
        }
        $this->creationDate = new \DateTime("now");
    }
    
    abstract function createMessage(UserId $senderId, string $text): Message;

    function id(): int {
        return $this->id;
    }
    
    function allMessages(): Collection {
        return $this->allMessages;
    }
    
    protected function failIfNotAParticipant(UserId $userId): void {
        if(!$this->participants->containsKey($userId)) {
            throw new \RuntimeException("User $userId not a participant of dialog");
        }
    }
            
    function removeMessageFor(int $participantId, int $messageId): void {
        $this->failIfNotAParticipant($participantId);
        $participantHistory = $this->getParticipantHistory($participantId);
        
        if(!$participantHistory->containsKey($messageId)) {
            throw new \RuntimeException('Cannot find message in history');
        }
        $participantHistory->remove($messageId);
    }
    
    function removeMessagesFor(int $participantId, array $messagesIds): void {
        $this->failIfNotAParticipant($participantId);
        $participantHistory = $this->getParticipantHistory($participantId);
        
        foreach($messagesIds as $messageId) {
            if(!$participantHistory->containsKey($messageId)) {
                throw new \RuntimeException('Cannot find message in history');
            }
            $participantHistory->remove($messageId);
        }
    }
    
    function cleanHistoryFor(int $participantId): void {
        $this->failIfNotAParticipant($participantId);
        
        $participantHistory = $this->getParticipantHistory($participantId);
        $participantHistory->clear();
    }
    
    function getParticipantHistory(int $participantId): MessagesHistory {
        foreach($this->messagesHistories as $history) {
            if($history->userId() === $participantId) {
                return $history;
            }
        }
    }
    
    function participants(): Collection {
        return $this->participants;
    }
    
    function messagesHistories(): Collection {
        return $this->messagesHistories;
    }
}
