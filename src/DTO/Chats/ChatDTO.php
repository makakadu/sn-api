<?php
declare(strict_types=1);
namespace App\DTO\Chats;

use App\DTO\CreatorDTO;

class ChatDTO implements ChatDTOInterface {
    public string $id;
    public string $clientId;
    public array $participants;
    public string $startedBy;
    public int $createdAt;
    public string $type;
    public ?MessageDTO $lastMessage;
    public array $messages;
    public ?string $prevMessageCursor;
    public ?string $nextMessageCursor;
    public ?string $lastReadMessageId;
    public int $unreadMessagesCount;
    
    public function __construct(
        string $id,
        string $clientId,
        array $participants,
        string $type,
        string $startedBy,
        int $createdAt,
        ?MessageDTO $lastMessage,
        array $messages,
        ?string $prevMessageCursor,
        ?string $nextMessageCursor,
        ?string $lastReadMessageId,
        int $unreadMessagesCount
    ) {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->participants = $participants;
        $this->type = $type;
        $this->startedBy = $startedBy;
        $this->createdAt = $createdAt;
        $this->lastMessage = $lastMessage;
        $this->messages = $messages;
        $this->prevMessageCursor = $prevMessageCursor;
        $this->nextMessageCursor = $nextMessageCursor;
        $this->lastReadMessageId = $lastReadMessageId;
        $this->unreadMessagesCount = $unreadMessagesCount;
    }
}
