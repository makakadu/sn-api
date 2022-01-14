<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetReactions;

use App\Application\BaseRequest;

class GetReactionsRequest implements BaseRequest {
    public ?string $requesterId;
    public string $postId;
    /** @var mixed $offsetCommentId */
    public $offsetCommentId;
    /** @var mixed $limit */
    public $limit;

    /**
     * @param mixed $offsetCommentId
     * @param mixed $limit
     */
    function __construct(?string $requesterId, string $postId, $offsetCommentId, $limit) {
        $this->requesterId = $requesterId;
        $this->postId = $postId;
        $this->offsetCommentId = $offsetCommentId;
        $this->limit = $limit;
    }

}
