<?php
declare(strict_types=1);
namespace App\Domain\Model\Dialogue;

use Doctrine\Common\Collections\{ArrayCollection, Collection};
use App\Domain\Model\Identity\User\User;

class MessagesHistory {
    public $version;
    private int $id;
    private Chat $chat;
    private int $userId;
    private Collection $messages;
    private array $unreadMessagesIds = [];
    private \DateTime $creationDate;

    function __construct(Chat $chat, int $userId) {
        $this->id = \uniqid();
        $this->userId = $userId;
        $this->chat = $chat;
        $this->messages = new ArrayCollection();
        $this->creationDate = new \DateTime("now");
    }
    
    function chat(): Chat {
        return $this->chat;
    }
    
    function unreadMessagesIds(): array {
        return $this->unreadMessagesIds;
    }
    
    function addMessage(Message $message): void {
        $this->messages[$message->id()] = $message;
        $this->unreadMessagesIds[$message->id()] = $message->id();
    }
    
    function deleteMessage(int $messageId): void {
        $this->readMessages->remove($messageId);
    }
    
    function deleteUnreadMessageId(int $messageId): void {
        unset($this->unreadMessagesIds[$messageId]);
    }

    function clear(): void {
        $this->messages->clear();
        $this->unreadMessagesIds = [];
    }
    
    function messages(): Collection {
        return $this->messages;
    }
    
    function id(): int {
        return $this->id;
    }
    
    function userId(): int {
        return $this->userId;
    }
}
