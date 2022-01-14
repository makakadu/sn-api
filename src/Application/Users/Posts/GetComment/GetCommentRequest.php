<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetComment;

use App\Application\BaseRequest;

class GetCommentRequest implements BaseRequest {
    public ?string $requesterId;
    public string $commentId;
    /** @var mixed $repliesCount */
    public $repliesCount;
    
    /** @param mixed $repliesCount */
    function __construct(?string $requesterId, string $commentId, $repliesCount) {
        $this->requesterId = $requesterId;
        $this->commentId = $commentId;
        $this->repliesCount = $repliesCount;
    }

}
