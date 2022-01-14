<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\GetPart;

use App\Application\BaseResponse;

class GetPartResponse implements BaseResponse {
    
    /** @var array<int, PagePostDTO> $items */
    public array $items;

    /** @param array<int, PagePostDTO> $items */
    public function __construct(array $items) {
        $this->items = $items;
    }

}