<?php
declare(strict_types=1);
namespace App\DTO\Chats;

use App\DTO\CreatorDTO;

class ChatDTO implements ChatDTOInterface {
    public string $id;
    public array $participants;
    public string $startedBy;
    public int $createdAt;
    public string $type;
    public ?MessageDTO $lastMessage;
    public array $messages;
    public ?string $messageCursor;
    public ?string $lastReadMessageId;
    public int $unreadMessagesCount;
    
    public function __construct(
        string $id,
        array $participants,
        string $type,
        string $startedBy,
        int $createdAt,
        ?MessageDTO $lastMessage,
        array $messages,
        ?string $messageCursor,
        ?string $lastReadMessageId,
        int $unreadMessagesCount
    ) {
        $this->id = $id;
        $this->participants = $participants;
        $this->type = $type;
        $this->startedBy = $startedBy;
        $this->createdAt = $createdAt;
        $this->lastMessage = $lastMessage;
        $this->messages = $messages;
        $this->messageCursor = $messageCursor;
        $this->lastReadMessageId = $lastReadMessageId;
        $this->unreadMessagesCount = $unreadMessagesCount;
    }
}
