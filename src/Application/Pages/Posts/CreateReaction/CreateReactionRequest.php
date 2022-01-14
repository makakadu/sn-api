<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\CreateReaction;

use App\Application\BaseRequest;

class CreateReactionRequest implements BaseRequest {
    public string $requesterId;
    public string $postId;
    /** @var mixed $onBehalfOfPage */
    public $onBehalfOfPage;
    /** @var mixed $type */
    public $type;
    
    /**
     * @param mixed $type
     * @param mixed $onBehalfOfPage
     */
    public function __construct(string $requesterId, string $postId, $type, $onBehalfOfPage) {
        $this->requesterId = $requesterId;
        $this->postId = $postId;
        $this->type = $type;
        $this->onBehalfOfPage = $onBehalfOfPage;
    }

}
