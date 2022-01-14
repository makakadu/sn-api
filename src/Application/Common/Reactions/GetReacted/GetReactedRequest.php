<?php
declare(strict_types=1);
namespace App\Application\Common\Reactions\GetReacted;

use App\Application\BaseRequest;

class GetReactedRequest implements BaseRequest {
    public ?string $requesterId;
    public string $reactorId;
    
    /** @var mixed $offsetId */
    public $offsetId;
    /** @var mixed $count */
    public $count;
    /** @var mixed $types */
    public $types;
    
    /** @var mixed $commentsCount */
    public $commentsCount;
    /** @var mixed $commentsType */
    public $commentsType;
    /** @var mixed $commentsOrder */
    public $commentsOrder;
    
    /**
     * 
     * @param string|null $requesterId
     * @param string $reactorId
     * @param mixed $commentsCount
     * @param mixed $commentsType
     * @param mixed $commentsOrder
     * @param mixed $offsetId
     * @param mixed $count
     * @param mixed $types
     */
    function __construct(?string $requesterId, string $reactorId, $commentsCount, $commentsType, $commentsOrder, $offsetId, $count, $types) {
        $this->requesterId = $requesterId;
        $this->reactorId = $reactorId;
        $this->commentsCount = $commentsCount;
        $this->commentsType = $commentsType;
        $this->commentsOrder = $commentsOrder;
        $this->offsetId = $offsetId;
        $this->count = $count;
        $this->types = $types;
    }

}
