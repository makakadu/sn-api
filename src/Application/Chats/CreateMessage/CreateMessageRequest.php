<?php
declare(strict_types=1);
namespace App\Application\Chats\CreateMessage;

use App\Application\BaseRequest;

class CreateMessageRequest implements BaseRequest {
    public string $requesterId;
    public string $chatId;
    /** @var mixed $text */
    public $text;
    /** @var mixed $frontKey */
    public $frontKey;
            
    /**
     * @param mixed $text
     */
    function __construct(string $requesterId, string $chatId, $text, $frontKey) {
        $this->requesterId = $requesterId;
        $this->chatId = $chatId;
        $this->text = $text;
        $this->frontKey = $frontKey;
    }
}
