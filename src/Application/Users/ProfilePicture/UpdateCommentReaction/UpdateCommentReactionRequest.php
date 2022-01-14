<?php
declare(strict_types=1);
namespace App\Application\Users\ProfilePicture\UpdateCommentReaction;

use App\Application\BaseRequest;

class UpdateCommentReactionRequest implements BaseRequest {
    public string $requesterId;
    public string $reactionId;
    /** @var mixed $type */
    public $type;

    /**
     * @param mixed $type
     */
    function __construct(string $requesterId, string $reactionId, $type) {
        $this->requesterId = $requesterId;
        $this->reactionId = $reactionId;
        $this->type = $type;
    }

}
