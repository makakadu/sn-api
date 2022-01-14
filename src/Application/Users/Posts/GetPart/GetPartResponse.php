<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetPart;

use App\Application\BaseResponse;
use App\DTO\Users\UserPostDTO;

class GetPartResponse implements BaseResponse {
    public int $allPostsCount;
    
    /** @var array<int, UserPostDTO> $items */
    public array $items;
    public ?string $cursor;

    /** @param array<int, UserPostDTO> $items */
    public function __construct(array $items, ?string $cursor, int $allPostsCount) {
        $this->items = $items;
        $this->cursor = $cursor;
        $this->allPostsCount = $allPostsCount;
    }

}