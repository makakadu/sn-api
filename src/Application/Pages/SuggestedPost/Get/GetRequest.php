<?php
declare(strict_types=1);
namespace App\Application\Pages\SuggestedPost\Get;

use App\Application\BaseRequest;

class GetRequest implements BaseRequest {
    public ?string $requesterId;
    public string $postId;
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
    function __construct(?string $requesterId, string $postId, $commentsCount, $commentsType, $commentsOrder) {
        $this->requesterId = $requesterId;
        $this->postId = $postId;
        $this->commentsCount = $commentsCount;
        $this->commentsType = $commentsType;
        $this->commentsOrder = $commentsOrder;
    }

}
