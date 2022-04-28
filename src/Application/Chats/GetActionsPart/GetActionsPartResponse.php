<?php
declare(strict_types=1);
namespace App\Application\Chats\GetActionsPart;

use App\Application\BaseResponse;
use App\DTO\Chats\ChatDTO;
use App\DTO\Chats\MessageDTO;

class GetActionsPartResponse implements BaseResponse {
    
    /** @var array<int, MessageDTO> $items */
    public array $items;
    public ?int $cursor;

    /** @param array<int, MessageDTO> $items */
    public function __construct(array $items, ?int $cursor) {
        $this->items = $items;
        $this->cursor = $cursor;
    }

}