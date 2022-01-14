<?php
declare(strict_types=1);
namespace App\Application\Common\Feed;

use App\Application\BaseRequest;

class FeedRequest implements BaseRequest {
    public string $requesterId;
    /** @var mixed $cursor */
    public $cursor;
    /** @var mixed $count */
    public $count;
    
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
    function __construct(string $requesterId, $cursor, $count, $commentsCount, $commentsOrder, $commentsType) {
        $this->requesterId = $requesterId;
        $this->cursor = $cursor;
        $this->count = $count;
        $this->$commentsCount = $commentsCount;
        $this->$commentsType = $commentsType;
        $this->$commentsOrder = $commentsOrder;
    }

}
