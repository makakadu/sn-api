<?php
declare(strict_types=1);
namespace App\Application\Pages\SuggestedPost\GetPart;

use App\Application\BaseRequest;

class GetPartRequest implements BaseRequest {
    public ?string $requesterId;
    public string $pageId;
    
    /** @var mixed $offsetId */
    public $offsetId;
    /** @var mixed $count */
    public $count;
    
    /** @var mixed $commentsCount */
    public $commentsCount;
    /** @var mixed $commentsType */
    public $commentsType;
    /** @var mixed $commentsOrder */
    public $commentsOrder;
    
    
    /**
     * @param mixed $commentsCount
     * @param mixed $commentsType
     * @param mixed $commentsOrder
     */
    function __construct(?string $requesterId, string $pageId, $offsetId, $count, $commentsCount, $commentsType, $commentsOrder) {
        $this->requesterId = $requesterId;
        $this->pageId = $pageId;
        $this->offsetId = $offsetId;
        $this->count = $count;
        $this->commentsCount = $commentsCount;
        $this->commentsType = $commentsType;
        $this->commentsOrder = $commentsOrder;
    }

}
