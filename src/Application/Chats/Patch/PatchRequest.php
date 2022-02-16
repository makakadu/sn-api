<?php
declare(strict_types=1);
namespace App\Application\Chats\Patch;

use App\Application\BaseRequest;

class PatchRequest implements BaseRequest {
    public string $requesterId;
    public string $chatId;
    /** @var mixed $property */
    public $property;
    /** @var mixed $value */
    public $value;
    
    /**
     * @param mixed $property
     * @param mixed $value
     */
    function __construct(string $requesterId, string $chatId, $property, $value) {
        $this->requesterId = $requesterId;
        $this->chatId = $chatId;
        $this->property = $property;
        $this->value = $value;
    }
}
