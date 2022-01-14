<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\UpdateCommentReaction;

use App\Application\BaseRequest;

class UpdateCommentReactionRequest implements BaseRequest {
    public string $requesterId;
    public string $commentId;
    public string $reactionId;
    /** @var mixed $type */
    public $type;
    
    /** @param mixed $type */
    function __construct(string $requesterId, string $commentId, string $reactionId, $type) {
        $this->requesterId = $requesterId;
        $this->commentId = $commentId;
        $this->reactionId = $reactionId;
        $this->type = $type;
    }

}
