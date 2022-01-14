<?php
declare(strict_types=1);
namespace App\Application\Groups\CreatePost;

use App\Application\BaseRequest;

class CreatePostRequest implements BaseRequest {
    public $requesterId;
    public $text;
    public $attachments;
    public $disableComments;
    public $sharedId;
            
    function __construct($requesterId, $text, $attachments, $disableComments, $repostedId) {
        $this->requesterId = $requesterId;
        $this->text = $text;
        $this->attachments = $attachments;
        $this->disableComments = $disableComments;
        $this->repostedId = $repostedId;
    }

}
