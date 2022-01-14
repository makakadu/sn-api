<?php

declare(strict_types=1);

namespace App\Domain\Model\Dialogue;

use Doctrine\Common\Collections\{ArrayCollection, Collection};

use App\Domain\Model\Identity\BannedByUserException;
use App\Domain\Model\Identity\UserIsBannedException;
use App\Domain\Model\Identity\User\UserId;

class Dialogue extends Chat {
    private int $initiatorId;
    private int $secondUserId;
    
    function __construct(int $creatorId, array $participants) {
        parent::__construct($participants);
        $this->id = $this->createDialogueId($participants);
        $this->initiatorId = $creatorId;
        $this->secondUserId = $participants[0]->id() === $creatorId ? $participants[1]->id() : $participants[0]->id();
    }
    
    private function createDialogueId(array $participants): int {
        $firstId = $participants[0]->id();
        $secondId = $participants[1]->id();
        
        if($firstId > $secondId) {
            return $secondId . '-' . $firstId;
        } else {
            return $firstId . '-' . $secondId;
        }
    }
    
    function createMessage(UserId $senderId, string $text): Message {
        $this->failIfNotAParticipant($senderId);  
        $sender = $this->participants->get($senderId);
        
        foreach($this->participants as $participant) {
            if($participant->id() !== $senderId) {
                $receiver = $participant; break;
            }
        }

        if($sender->inBannedList($receiver->id())) {
            throw new UserIsBannedException("Receiver {$receiver->id()} is banned");
        } elseif($receiver->inBannedList($sender->id())) { 
            throw new BannedByUserException("Banned by receiving user {$receiver->id()}");
        } elseif($senderId === $this->firstId && !$this->isSecondAccepts) {
            throw new \RuntimeException("User $this->secondId doesn't accept messages"); // это тоже смахивает на настройку приватности
        } elseif($senderId === $this->secondId && !$this->isFirstAccepts) {
            throw new \RuntimeException("User $this->firstId doesn't accept messages"); // это тоже смахивает на настройку приватности
        }
        $message = new Message($sender, $this, $text);
        $senderHistory = $this->getParticipantHistory($senderId);
        $receiverHistory = $this->getParticipantHistory($receiver->id());
        
        $this->allMessages->add($message);
        $senderHistory->addMessage($message);
        $receiverHistory->addMessage($message);
        
        return $message;
    }
    
    function initiatorId(): int {
        return $this->initiatorId;
    }
    
    function secondUserId(): int {
        return $this->secondUserId;
    }
}
