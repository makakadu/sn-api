<?php
declare(strict_types=1);
namespace App\Application\Pages\SuggestedPost\Create;

use App\Application\BaseRequest;

class CreateRequest implements BaseRequest {
    public string $requesterId;
    public string $pageId;
    
    /** @var mixed $text */
    public $text;
    /** @var mixed $addSignature */
    public $addSignature;
    /** @var mixed $hideSignatureIfEdited */
    public $hideSignatureIfEdited;
    /** @var mixed $attachments */
    public $attachments;
    
    /**
     * @param mixed $text
     * @param mixed $attachments
     * @param mixed $addSignature
     * @param mixed $hideSignatureIfEdited
     */
    public function __construct(string $requesterId, string $pageId, $text, $addSignature, $hideSignatureIfEdited, $attachments) {
        $this->requesterId = $requesterId;
        $this->pageId = $pageId;
        $this->text = $text;
        $this->attachments = $attachments;
        $this->addSignature = $addSignature;
        $this->hideSignatureIfEdited = $hideSignatureIfEdited;
    }

}
