<?php
declare(strict_types=1);
namespace App\Application\Groups\CreatePost;

use App\Application\BaseRequest;

class CreatePostRequest implements BaseRequest {
    public string $requesterId;
    /** @var mixed $text */
    public $text;
    /** @var mixed $attachments */
    public $attachments;
    /** @var mixed $disableComments */
    public $disableComments;
    /** @var mixed $sharedId */
    public $sharedId;
            
    /**
     * @param mixed $text
     * @param mixed $attachments
     * @param mixed $disableComments
     * @param mixed $sharedId
     */
    function __construct(string $requesterId, $text, $attachments, $disableComments, $sharedId) {
        $this->requesterId = $requesterId;
        $this->text = $text;
        $this->attachments = $attachments;
        $this->disableComments = $disableComments;
        $this->sharedId = $sharedId;
    }

}
