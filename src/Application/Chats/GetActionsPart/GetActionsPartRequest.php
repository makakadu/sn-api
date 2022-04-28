<?php
declare(strict_types=1);
namespace App\Application\Chats\GetActionsPart;

use App\Application\BaseRequest;

class GetActionsPartRequest implements BaseRequest {
    public string $requesterId;
    public string $chatId;
    
    /** @var mixed $types */
    public $type;
    /** @var mixed $cursor */
    public $cursor;
    /** @var mixed $count */
    public $count;   
    
    /**
     * @param mixed $types
     * @param mixed $cursor
     * @param mixed $count
     */
    function __construct(string $requesterId, string $chatId, $types, $cursor, $count) {
        $this->requesterId = $requesterId;
        $this->chatId = $chatId;
        $this->types = $types;
        $this->cursor = $cursor;
        $this->count = $count;
    }

}
