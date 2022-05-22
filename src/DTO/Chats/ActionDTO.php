<?php
declare(strict_types=1);
namespace App\DTO\Chats;

use App\DTO\CreatorDTO;

class ActionDTO {
    public string $type;
    public string $chatId;
    public string $chatClientId;
    public string $initiatorId;
    public int $createdAt;
    public array $extraProps;
                        
    public function __construct(string $type, string $chatId, string $chatClientId, string $initiatorId, int $createdAt, array $extraProps = []) {
        $this->type = $type;
        $this->chatId = $chatId;
        $this->chatClientId = $chatClientId;
        $this->initiatorId = $initiatorId;
        $this->createdAt = $createdAt;
        $this->extraProps = $extraProps;
    }

}
