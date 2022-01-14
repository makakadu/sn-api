<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\Get;

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
    /** @var mixed $fields */
    public $fields;
    
    /**
     * @param mixed $commentsCount
     * @param mixed $commentsType
     * @param mixed $commentsOrder
     * @param mixed $fields
     */
    function __construct(?string $requesterId, string $postId, $commentsCount, $commentsType, $commentsOrder, $fields) {
        $this->requesterId = $requesterId;
        $this->postId = $postId;
        $this->commentsCount = $commentsCount;
        $this->commentsType = $commentsType;
        $this->commentsOrder = $commentsOrder;
        $this->fields = $fields;
    }

}
