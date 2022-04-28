<?php
declare(strict_types=1);
namespace App\Domain\Model\Chats;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Chats\Chat;

class Action {
    
    const CREATE_MESSAGE = 1;
    const DELETE_MESSAGE = 2;
    const DELETE_MESSAGE_FOR_ALL = 3;

    private int $id;
    private Chat $chat;
    private int $type;
    private string $initiatorId;
    private string $messageId;
    private string $messageClientId;
    private string $messageCreatorId;
    private \DateTime $createdAt;
    
    function __construct(Chat $chat, User $initiator, int $type, string $messageId, string $messageClientId, string $messageCreatorId) {
        $this->chat = $chat;
        $this->type = $type;
        $this->initiatorId = $initiator->id();
        $this->messageId = $messageId;
        $this->messageClientId = $messageClientId;
        $this->messageCreatorId = $messageCreatorId;
        $this->createdAt = new \DateTime();
        // При извлечении actions нужно сделать так, чтобы пользователь
    }
    
    public function getId(): int {
        return $this->id;
    }

    public function getChat(): Chat {
        return $this->chat;
    }

    public function getType(): int {
        return $this->type;
    }

    public function getInitiatorId(): string {
        return $this->initiatorId;
    }

    public function getMessageClientId(): string {
        return $this->messageClientId;
    }
    
    public function getMessageId(): string {
        return $this->messageId;
    }
    
    public function getMessageCreatorId(): string {
        return $this->messageCreatorId;
    }

    public function getCreatedAt(): \DateTime {
        return $this->createdAt;
    }


}
