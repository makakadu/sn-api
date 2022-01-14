<?php
declare(strict_types=1);
namespace App\Application\Users\Search;

class SearchResponse implements \App\Application\BaseResponse {
    public $items;
    public $count;
    public $cursor;
    
    public function __construct(array $dtos, int $count, $cursor) {
        $this->items = $dtos;
        $this->count = $count;
        $this->cursor = $cursor;
    }
}
