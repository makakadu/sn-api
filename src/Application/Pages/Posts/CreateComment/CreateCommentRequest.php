<?php //
declare(strict_types=1);
namespace App\Application\Pages\Posts\CreateComment;

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
    public ?string $asPage;
    
    /**
     * @param mixed $text
     * @param mixed $repliedId
     * @param mixed $attachment
     * @param mixed $asPage
     */
    function __construct(string $requesterId, string $postId, $text, $repliedId, $attachment, ?string $asPage) {
        $this->requesterId = $requesterId;
        $this->postId = $postId;
        $this->text = $text;
        $this->repliedId = $repliedId;
        $this->attachment = $attachment;
        $this->asPage = $asPage;
    }

}
