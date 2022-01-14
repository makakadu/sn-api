<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\UpdateComment;

use App\Application\BaseRequest;

class UpdateCommentRequest implements BaseRequest {
    public string $requesterId;
    public string $commentId;
    
    /** @var mixed $text */
    public $text;
    /** @var mixed $attachmentId */
    public $attachmentId;
    
    /**
     * @param mixed $text
     * @param mixed $attachmentId
     */
    public function __construct(string $requesterId, string $commentId, $text, $attachmentId) {
        $this->requesterId = $requesterId;
        $this->commentId = $commentId;
        $this->text = $text;
        $this->attachmentId = $attachmentId;
    }

}
