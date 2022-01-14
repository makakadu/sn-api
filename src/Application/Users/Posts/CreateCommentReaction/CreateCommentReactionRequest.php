<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\CreateCommentReaction;

use App\Application\BaseRequest;

class CreateCommentReactionRequest implements BaseRequest {
    public string $requesterId;
    public string $commentId;
    /** @var mixed $type */
    public $type;
    
    /** @param mixed $type */
    function __construct(string $requesterId, string $commentId, $type) {
        $this->requesterId = $requesterId;
        $this->commentId = $commentId;
        $this->type = $type;
    }

}
