<?php
declare(strict_types=1);
namespace App\Application\Chats\GetPart;

use App\Application\BaseResponse;
use App\DTO\Chats\ChatDTO;

class GetPartResponse implements BaseResponse {
    
    /** @var array<int, ChatDTO> $items */
    public array $items;
    public ?string $cursor;
    public int $allCount;

    /** @param array<int, ChatDTO> $items */
    public function __construct(array $items, ?string $cursor, int $allCount) {
        $this->items = $items;
        $this->cursor = $cursor;
        $this->allCount = $allCount;
    }

}