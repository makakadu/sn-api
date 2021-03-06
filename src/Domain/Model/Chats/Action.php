<?php
declare(strict_types=1);
namespace App\Domain\Model\Chats;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Chats\Chat;

abstract class Action {
    private Chat $chat;
    private int $id;
    private string $chatId;
    private string $chatClientId;
    private string $type;
    private string $initiatorId;
    private \DateTime $createdAt;
    
    function __construct(Chat $chat, string $initiatorId, string $type) {
        $this->type = $type;
        $this->initiatorId = $initiatorId;
        $this->createdAt = new \DateTime();
        $this->chat = $chat;
        $this->chatId = $chat->id();
        $this->chatClientId = $chat->clientId();
    }
    
    function getChat(): Chat {
        return $this->chat;
    }
    
    public function getId(): int {
        return $this->id;
    }
    
    public function getChatClientId(): string {
        return $this->chatClientId;
    }

    public function getChatId(): string {
        return $this->chatId;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getInitiatorId(): string {
        return $this->initiatorId;
    }

    public function getCreatedAt(): \DateTime {
        return $this->createdAt;
    }

    /**
     * @template T
     * @param ActionVisitor <T> $visitor
     * @return T
     */
    abstract function acceptActionVisitor(ActionVisitor $visitor);
    
}
