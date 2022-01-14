<?php
declare(strict_types=1);
namespace App\Application\Common\Feed;

use App\Application\BaseResponse;
use App\DTO\Users\UserPostDTO;

class FeedResponse implements BaseResponse {
    /** @var array<int, UserPostDTO> $items */
    public array $items;
    public ?string $cursor;

    /** @param array<int, UserPostDTO> $items */
    public function __construct(array $items, ?string $cursor) {
        $this->items = $items;
        $this->cursor = $cursor;
    }

}