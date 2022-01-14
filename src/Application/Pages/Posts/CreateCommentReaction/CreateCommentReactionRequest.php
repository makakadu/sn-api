<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\CreateCommentReaction;

use App\Application\BaseRequest;

class CreateCommentReactionRequest implements BaseRequest {
    public string $requesterId;
    public string $commentId;
    /** @var mixed $type */
    public $type;
    /** @var mixed $onBehalfOfPage */
    public $onBehalfOfPage;
    
    /** @param mixed $type */
    function __construct(string $requesterId, string $commentId, $type, $onBehalfOfPage) {
        $this->requesterId = $requesterId;
        $this->commentId = $commentId;
        $this->type = $type;
        $this->onBehalfOfPage = $onBehalfOfPage;
    }

}
