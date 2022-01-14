<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\UpdateReaction;

use App\Application\BaseRequest;

class UpdateReactionRequest implements BaseRequest {
    public string $requesterId;
    public string $postId;
    public string $reactionId;
    /** @var mixed $type */
    public $type;
    
    /** @param mixed $payload */
    function __construct(string $requesterId, string $postId, string $reactionId, $type) {
        $this->requesterId = $requesterId;
        $this->postId = $postId;
        $this->reactionId = $reactionId;
        $this->type = $type;
    }

}
