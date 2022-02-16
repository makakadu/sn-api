<?php
declare(strict_types=1);
namespace App\DTO\Chats;

use App\DTO\CreatorDTO;

class PartialChatDTO implements ChatDTOInterface {
    public string $id;
    
    public function __construct(
        string $id
    ) {
        $this->id = $id;
    }
}
