<?php
declare(strict_types=1);
namespace App\Application\Pages\SuggestedPost\Put;

use App\Application\BaseRequest;

class PutRequest implements BaseRequest {
    public string $requesterId;
    public string $postId;
    /** @var mixed $text */
    public $text;
    /** @var mixed $isPublic */
    public $isPublic;
    /** @var mixed $attachments */
    public $attachments;
    /** @var mixed $disableComments */
    public $disableComments;
    /** @var mixed $disableReactions */
    public $disableReactions;
    /** @var mixed $shared */
    public $shared;
            
    /**
     * @param mixed $text
     * @param mixed $isPublic
     * @param mixed $attachments
     * @param mixed $disableComments
     */
    function __construct(string $requesterId, string $postId, $text, $isPublic, $attachments, $disableComments, $disableReactions) {
        $this->requesterId = $requesterId;
        $this->postId = $postId;
        $this->text = $text;
        $this->isPublic = $isPublic;
        $this->attachments = $attachments;
        $this->disableComments = $disableComments;
        $this->disableReactions = $disableReactions;
    }
}
