<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\DeleteComment;

use App\Application\BaseRequest;

class DeleteCommentRequest implements BaseRequest {
    public string $commentId;
    public string $requesterId;
    
    function __construct(string $commentId, string $requesterId) {
        $this->commentId = $commentId;
        $this->requesterId = $requesterId;
    }

}
