<?php
declare(strict_types=1);
namespace App\Application\Chats\Get;

use App\Application\BaseRequest;

class GetRequest implements BaseRequest {
    public string $requesterId;
    public string $chatId;
    /** @var mixed $messagesCursor */
    public $messagesCursor;
    /** @var mixed $messagesCount */
    public $messagesCount;

    function __construct(string $requesterId, string $chatId, $messagesCursor, $messagesCount) {
        $this->requesterId = $requesterId;
        $this->chatId = $chatId;
        $this->messagesCursor = $messagesCursor;
        $this->messagesCount = $messagesCount;
    }

}
