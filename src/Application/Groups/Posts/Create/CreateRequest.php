<?php
declare(strict_types=1);
namespace App\Application\Groups\Posts\Create;

use App\Application\BaseRequest;

class CreateRequest implements BaseRequest {
    public string $requesterId;
    public string $groupId;
    /** @var mixed $text */
    public $text;
    /** @var mixed $onBehalfOfGroup */
    public $onBehalfOfGroup;
    /** @var mixed $attachments */
    public $attachments;
    /** @var mixed $disableComments */
    public $disableComments;
    /** @var mixed $shared */
    public $shared;
    /** @var mixed $addSignature */
    public $addSignature;
    
    /**
     * @param mixed $text
     * @param mixed $onBehalfOfGroup
     * @param mixed $attachments
     * @param mixed $disableComments
     * @param mixed $shared
     * @param mixed $addSignature
     */
    function __construct(string $requesterId, string $groupId, $text, $onBehalfOfGroup, $attachments, $disableComments, $shared, $addSignature) {
        $this->requesterId = $requesterId;
        $this->groupId = $groupId;
        $this->text = $text;
        $this->onBehalfOfGroup = $onBehalfOfGroup;
        $this->attachments = $attachments;
        $this->disableComments = $disableComments;
        $this->shared = $shared;
        $this->addSignature = $addSignature;
    }

}
