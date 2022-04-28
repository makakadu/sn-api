<?php
declare(strict_types=1);
namespace App\Application\Chats\PatchMessage;

use App\Application\BaseRequest;

class PatchMessageRequest implements BaseRequest {
    public string $requesterId;
    public string $messageId;
    /** @var mixed $property */
    public $property;
    /** @var mixed $value */
    public $value;
    /** @var mixed $placeId */
    public $placeId;
    
    /**
     * @param mixed $property
     * @param mixed $value
     * @param mixed $placeId
     */
    function __construct(string $requesterId, string $messageId, $property, $value, $placeId) {
        $this->requesterId = $requesterId;
        $this->messageId = $messageId;
        $this->property = $property;
        $this->value = $value;
        $this->placeId = $placeId;
    }
}
