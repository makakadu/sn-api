<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\DeleteCommentReaction;

use App\Application\BaseRequest;

class DeleteCommentReactionRequest implements BaseRequest {
    public string $requesterId;
    public string $commentId;
    public string $reactionId;
            
    public function __construct(string $requesterId, string $commentId, string $reactionId) {
        $this->requesterId = $requesterId;
        $this->commentId = $commentId;
        $this->reactionId = $reactionId;
    }
  
}
