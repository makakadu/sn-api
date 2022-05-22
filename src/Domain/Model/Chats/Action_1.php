<?php
declare(strict_types=1);
namespace App\Domain\Model\Chats;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Chats\Chat;

abstract class Action_1 {
    private int $id;
    private string $chatId;
    private int $type;
    private string $initiatorId;
    private \DateTime $createdAt;
    
    function __construct(Chat $chat, User $initiator, int $type) {
        $this->chatId = $chat->id();
        $this->type = $type;
        $this->initiatorId = $initiator->id();
        $this->createdAt = new \DateTime();
    }
    
    public function getId(): int {
        return $this->id;
    }

    public function getChatId(): string {
        return $this->chatId;
    }

    public function getType(): int {
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
