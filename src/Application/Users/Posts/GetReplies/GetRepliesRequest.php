<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetReplies;

use App\Application\BaseRequest;

class GetRepliesRequest implements BaseRequest {
    public ?string $requesterId;
    /** @var mixed $commentId */
    public $commentId;
    /** @var mixed $offsetId */
    public $offsetId;
    /** @var mixed $count */
    public $count;

    /**
     * @param mixed $commentId
     * @param mixed $offsetId
     * @param mixed $count
     */
    function __construct(?string $requesterId, $commentId, $offsetId, $count) {
        $this->requesterId = $requesterId;
        $this->commentId = $commentId;
        $this->offsetId = $offsetId;
        $this->count = $count;
    }

}
