<?php
declare(strict_types=1);
namespace App\Domain\Model\Chats;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Chats\Chat;

class ChatAction {

    private int $id;
    private Chat $chat;
    private string $type;
    private string $initiatorId;
    private string $messageId;
    private string $messageClientId;
    private string $messageCreatorId;
    private \DateTime $createdAt;
    private array $extraFields;
    
    function __construct(
        Chat $chat, User $initiator, string $type, string $messageId,
        string $messageClientId, string $messageCreatorId, array $extraFields = []
    ) {
        $this->chat = $chat;
        $this->type = $type;
        $this->initiatorId = $initiator->id();
        $this->messageId = $messageId;
        $this->messageClientId = $messageClientId;
        $this->messageCreatorId = $messageCreatorId;
        $this->createdAt = new \DateTime();
        $this->extraFields = $extraFields;
    }
    
    public function getId(): int {
        return $this->id;
    }

    public function getChat(): Chat {
        return $this->chat;
    }

    public function getType(): string {
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

    public function getExtraFields(): array {
        return $this->extraFields;
    }
}
