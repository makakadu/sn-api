<?php
declare(strict_types=1);
namespace App\Application\Users\Search;

class SearchRequest implements \App\Application\BaseRequest {
    public ?string $requesterId;
    public string $text;
    public $cursor;
    public $count;
    public $fields;
    
    public function __construct(?string $requesterId, string $text, $cursor, $count, $fields) {
        $this->requesterId = $requesterId;
        $this->text = $text;
        $this->cursor = $cursor;
        $this->count = $count;
        $this->fields = $fields;
    }

}
