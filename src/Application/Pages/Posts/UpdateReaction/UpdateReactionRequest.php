<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\UpdateReaction;

use App\Application\BaseRequest;

class UpdateReactionRequest implements BaseRequest {
    public string $requesterId;
    public string $postId;
    public string $reactionId;
    /** @var mixed $type */
    public $type;
    
    /** @param mixed $type */
    function __construct(string $requesterId, string $commentId, string $reactionId, $type) {
        $this->requesterId = $requesterId;
        $this->postId = $commentId;
        $this->reactionId = $reactionId;
        $this->type = $type;
    }

}
