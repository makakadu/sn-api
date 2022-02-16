<?php
declare(strict_types=1);
namespace App\DTO\Chats;

use App\DTO\CreatorDTO;

class MessageDTO {
    public string $id;
    public CreatorDTO $creator;
    public string $chatId;
    public string $text;
    public int $createdAt;
    public string $frontKey;
    public array $readBy;
    
    public function __construct(string $id, CreatorDTO $creator, string $chatId, string $text, int $createdAt, string $frontKey, array $readBy) {
        $this->id = $id;
        $this->creator = $creator;
        $this->chatId = $chatId;
        $this->text = $text;
        $this->createdAt = $createdAt;
        $this->frontKey = $frontKey;
        $this->readBy = $readBy;
    }

}
