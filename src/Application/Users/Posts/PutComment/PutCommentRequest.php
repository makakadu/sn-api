<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\PutComment;

use App\Application\BaseRequest;

class PutCommentRequest implements BaseRequest {
    public string $requesterId;
    public string $commentId;
    /** @var mixed $text */
    public $text;
    /** @var mixed $attachment */
    public $attachment;
            
    /**
     * @param mixed $text
     * @param mixed $attachment
     */
    function __construct(string $requesterId, string $commentId, $text, $attachment) {
        $this->requesterId = $requesterId;
        $this->commentId = $commentId;
        $this->text = $text;
        $this->attachment = $attachment;
    }
}
