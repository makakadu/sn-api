<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\DeleteReaction;

use App\Application\BaseRequest;

class DeleteReactionRequest implements BaseRequest {
    public string $requesterId;
    public string $postId;
    public string $reactionId;
            
    public function __construct(string $requesterId, string $postId, string $reactionId) {
        $this->requesterId = $requesterId;
        $this->postId = $postId;
        $this->reactionId = $reactionId;
    }

}
