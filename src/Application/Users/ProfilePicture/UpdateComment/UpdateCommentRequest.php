<?php
declare(strict_types=1);
namespace App\Application\Users\ProfilePicture\UpdateComment;

use App\Application\BaseRequest;

class UpdateCommentRequest implements BaseRequest {
    public string $requesterId;
    public string $commentId;
    /** @var array<mixed> $payload */
    public $payload;
    
    /**
     * @param array<mixed> $payload
     */
    function __construct(string $requesterId, string $commentId, $payload) {
        $this->requesterId = $requesterId;
        $this->commentId = $commentId;
        $this->payload = $payload;
    }

}
