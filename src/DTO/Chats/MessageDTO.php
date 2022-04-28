<?php
declare(strict_types=1);
namespace App\DTO\Chats;

use App\DTO\CreatorDTO;

class MessageDTO {
    public string $id;
    public string $clientId;
    public CreatorDTO $creator;
    public string $chatId;
    public string $text;
    public int $createdAt;
    public array $readBy;
    
    public function __construct(string $id, string $clientId, CreatorDTO $creator, string $chatId, string $text, int $createdAt, array $readBy) {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->creator = $creator;
        $this->chatId = $chatId;
        $this->text = $text;
        $this->createdAt = $createdAt;
        $this->readBy = $readBy;
    }

}
