<?php //
declare(strict_types=1);
namespace App\Application\Users\Posts\CreateComment;

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
    public ?string $onBehalfOfPage;
    
    /**
     * @param mixed $text
     * @param mixed $repliedId
     * @param mixed $attachment
     * @param mixed $onBehalfOfPage
     */
    function __construct(string $requesterId, string $postId, $text, $repliedId, $attachment, ?string $onBehalfOfPage) {
        $this->requesterId = $requesterId;
        $this->postId = $postId;
        $this->text = $text;
        $this->repliedId = $repliedId;
        $this->attachment = $attachment;
        $this->onBehalfOfPage = $onBehalfOfPage;
    }

}
