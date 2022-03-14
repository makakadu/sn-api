<?php
declare(strict_types=1);
namespace App\Domain\Model\Chats;

use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\EntityTrait;
use Ulid\Ulid;

class Participant {
    use EntityTrait;
    
    private User $user;
    private Collection $messages;
    private Chat $chat;
    private ?string $lastReadMessageId;
    private ?string $lastMessageId;
    
    public function __construct(User $user, Chat $chat) {
        $this->id = (string)Ulid::generate(true);
        $this->user = $user;
        $this->messages = new ArrayCollection();
        $this->chat = $chat;
        $this->lastReadMessageId = null;
        $this->lastMessageId = null;
        $this->createdAt = new \DateTime('now');
    }
    
    function read(Message $message): void {
        $this->lastReadMessageId = $message->id();
    }
    
    function clearHistory(): void {
        $this->messages = new ArrayCollection();
        $this->lastMessageId = null;
        $this->lastReadMessageId = null;
    }
    
    function user(): User {
        return $this->user;
    }
    
    function addMessage(Message $message): void {
        $this->messages->add($message);
        $this->lastMessageId = $message->id();
    }
    
    function readMessage(Message $message): void {
        $this->lastReadMessageId = $message->id();
    }
    
    function messages(): Collection {
        return $this->messages;
    }

    public function lastReadMessageId(): ?string {
        return $this->lastReadMessageId;
    }


}
