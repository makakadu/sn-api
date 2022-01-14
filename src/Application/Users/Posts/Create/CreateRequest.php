<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\Create;

use App\Application\BaseRequest;

class CreateRequest implements BaseRequest {
    public string $requesterId;
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
     * @param mixed $disableReactions
     * @param mixed $shared
     */
    function __construct(string $requesterId, $text, $isPublic, $attachments, $disableComments, $disableReactions, $shared) {
        $this->requesterId = $requesterId;
        $this->text = $text;
        $this->isPublic = $isPublic;
        $this->attachments = $attachments;
        $this->disableComments = $disableComments;
        $this->disableReactions = $disableReactions;
        $this->shared = $shared;
    }
}
