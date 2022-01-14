<?php //
declare(strict_types=1);
namespace App\Application\Users\Posts\GetCommentReactions;

use App\Application\BaseRequest;

class GetCommentReactionsRequest implements BaseRequest {
    public ?string $requesterId;
    public string $commentId;
    /** @var mixed $offsetId */
    public $offsetId;
    /** @var mixed $limit */
    public $limit;

    /**
     * @param mixed $offsetId
     * @param mixed $limit
     */
    function __construct(?string $requesterId, string $commentId, $offsetId, $limit) {
        $this->requesterId = $requesterId;
        $this->commentId = $commentId;
        $this->offsetId = $offsetId;
        $this->limit = $limit;
    }

}
