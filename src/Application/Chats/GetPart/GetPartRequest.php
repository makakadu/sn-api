<?php
declare(strict_types=1);
namespace App\Application\Chats\GetPart;

use App\Application\BaseRequest;

class GetPartRequest implements BaseRequest {
    public string $requesterId;
    public string $userId;
    public ?string $interlocutorId;
    
    /** @var mixed $cursor */
    public $cursor;
    /** @var mixed $count */
    public $count;
    /** @var mixed $type */
    public $type;
    /** @var mixed $messagesCount */
    public $messagesCount;
    /** @var mixed $onlyUnread */
    public $onlyUnread;
    /** @var mixed $fields */
    public $fields;
    
    /**
     * @param mixed $cursor
     * @param mixed $count
     */
    function __construct(string $requesterId, string $userId, ?string $interlocutorId, $cursor, $count, $type, $messagesCount, $onlyUnread, $fields) {
        $this->requesterId = $requesterId;
        $this->userId = $userId;
        $this->interlocutorId = $interlocutorId;
        $this->cursor = $cursor;
        $this->count = $count;
        $this->type = $type;
        $this->messagesCount = $messagesCount;
        $this->onlyUnread = $onlyUnread;
        $this->fields = $fields;
    }

}
