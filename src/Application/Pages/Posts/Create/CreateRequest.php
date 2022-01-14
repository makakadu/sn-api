<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\Create;

use App\Application\BaseRequest;

class CreateRequest implements BaseRequest {
    public string $requesterId;
    public string $pageId;
    /** @var mixed $text */
    public $text;
    /** @var mixed $attachments */
    public $attachments;
    /** @var mixed $disableComments */
    public $disableComments;
    /** @var mixed $disableReactions */
    public $disableReactions;
    /** @var mixed $shared */
    public $shared;
    /** @var mixed $addSignature */
    public $addSignature;
    /** @var mixed $suggestedId*/
    public $suggestedId;
            
    /**
     * @param mixed $text
     * @param mixed $isPublic
     * @param mixed $attachments
     * @param mixed $disableComments
     * @param mixed $disableReactions
     * @param mixed $shared
     * @param mixed $addSignature
     * @param mixed $suggestedId
     */
    function __construct(string $requesterId, string $pageId, $text, $attachments, $disableComments, $disableReactions, $shared, $addSignature, $suggestedId) {
        $this->requesterId = $requesterId;
        $this->pageId = $pageId;
        $this->text = $text;
        $this->attachments = $attachments;
        $this->disableComments = $disableComments;
        $this->disableReactions = $disableReactions;
        $this->shared = $shared;
        $this->addSignature = $addSignature;
        $this->suggestedId = $suggestedId;
    }
}
