<?php
declare(strict_types=1);
namespace App\Application\Chats\GetMessagesPart;

use App\Application\BaseRequest;

class GetMessagesPartRequest implements BaseRequest {
    public string $requesterId;
    public string $chatId;
    
    /** @var mixed $cursor */
    public $cursor;
    /** @var mixed $count */
    public $count;   
    /** @var mixed $order */
    public $order;   
    
    /**
     * @param mixed $cursor
     * @param mixed $count
     * @param mixed $order
     */
    function __construct(string $requesterId, string $chatId, $cursor, $count, $order) {
        $this->requesterId = $requesterId;
        $this->chatId = $chatId;
        $this->cursor = $cursor;
        $this->count = $count;
        $this->order = $order;
    }

}
