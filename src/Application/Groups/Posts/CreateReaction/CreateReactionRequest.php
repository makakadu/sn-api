<?php
declare(strict_types=1);
namespace App\Application\Groups\Posts\CreateReaction;

use App\Application\BaseRequest;

class CreateReactionRequest implements BaseRequest {
    public string $requesterId;
    public string $postId;
    /** @var mixed $onBehalfOfGroup */
    public $onBehalfOfGroup;
    /** @var mixed $type */
    public $type;
    
    /**
     * @param mixed $type
     * @param mixed $onBehalfOfGroup
     */
    public function __construct(string $requesterId, string $postId, $type, $onBehalfOfGroup) {
        $this->requesterId = $requesterId;
        $this->postId = $postId;
        $this->type = $type;
        $this->onBehalfOfGroup = $onBehalfOfGroup;
    }

}
