<?php
declare(strict_types=1);
namespace App\Application\Chats\GetMessagesPart;

use App\Application\BaseResponse;
use App\DTO\Chats\MessageDTO;

class GetMessagesPartResponse implements BaseResponse {
    
    /** @var array<int, MessageDTO> $items */
    public array $items;
    public ?string $prevCursor;
    public ?string $nextCursor;

    /** @param array<int, MessageDTO> $items */
    public function __construct(array $items, ?string $prevCursor, ?string $nextCursor) {
        $this->items = $items;
        $this->prevCursor = $prevCursor;
        $this->nextCursor = $nextCursor;
    }

}