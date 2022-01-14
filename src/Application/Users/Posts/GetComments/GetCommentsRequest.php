<?php //
declare(strict_types=1);
namespace App\Application\Users\Posts\GetComments;

use App\Application\BaseRequest;

class GetCommentsRequest implements BaseRequest {
    public ?string $requesterId;
    /** @var mixed $postId */
    public $postId;
    /** @var mixed $offsetCommentId */
    public $offsetCommentId;
    /** @var mixed $limit */
    public $limit;
    /** @var mixed $type */
    public $type;
    /** @var mixed $order */
    public $order;

    /**
     * @param mixed $postId
     * @param mixed $offsetCommentId
     * @param mixed $limit
     * @param mixed $type
     * @param mixed $order
     */
    function __construct(?string $requesterId, $postId, $offsetCommentId, $limit, $type, $order) {
        $this->requesterId = $requesterId;
        $this->postId = $postId;
        $this->offsetCommentId = $offsetCommentId;
        $this->limit = $limit;
        $this->type = $type;
        $this->order = $order;
    }

}
