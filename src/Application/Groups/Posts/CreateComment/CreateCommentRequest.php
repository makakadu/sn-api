<?php //
declare(strict_types=1);
namespace App\Application\Groups\Posts\CreateComment;

use App\Application\BaseRequest;

class CreateCommentRequest implements BaseRequest {
    public string $requesterId;
    public string $postId;
    /** @var mixed $text */
    public $text;
    /** @var mixed $repliedId */
    public $repliedId;
    /** @var mixed $attachment */
    public $attachment;
    /** @var mixed $onBehalfOfGroup */
    public $onBehalfOfGroup;
    
    /**
     * @param mixed $text
     * @param mixed $repliedId
     * @param mixed $attachment
     * @param mixed $onBehalfOfGroup
     */
    function __construct(string $requesterId, string $postId, $text, $repliedId, $attachment, $onBehalfOfGroup) {
        $this->requesterId = $requesterId;
        $this->postId = $postId;
        $this->text = $text;
        $this->repliedId = $repliedId;
        $this->attachment = $attachment;
        $this->onBehalfOfGroup = $onBehalfOfGroup;
    }

}
