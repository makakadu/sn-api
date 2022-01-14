<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetPart;

use App\Application\BaseRequest;

class GetPartRequest implements BaseRequest {
    public ?string $requesterId;
    public string $pageId;
    
    /** @var mixed $cursor */
    public $cursor;
    /** @var mixed $count */
    public $count;
    /** @var mixed $order */
    public $order;
    
    /** @var mixed $commentsCount */
    public $commentsCount;
    /** @var mixed $commentsType */
    public $commentsType;
    /** @var mixed $commentsOrder */
    public $commentsOrder;
    
    
    /**
     * @param mixed $cursor
     * @param mixed $count
     * @param mixed $commentsCount
     * @param mixed $commentsType
     * @param mixed $commentsOrder
     */
    function __construct(?string $requesterId, string $pageId, $cursor, $count, $order, $commentsCount, $commentsType, $commentsOrder) {
        $this->requesterId = $requesterId;
        $this->pageId = $pageId;
        $this->cursor = $cursor;
        $this->count = $count;
        $this->order = $order;
        $this->commentsCount = $commentsCount;
        $this->commentsType = $commentsType;
        $this->commentsOrder = $commentsOrder;
    }

}
