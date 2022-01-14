<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\CreateReaction;

use App\Application\BaseRequest;

class CreateReactionRequest implements BaseRequest {
    public string $requesterId;
    public string $postId;
    public ?string $asPageId;
    public $type;
    
    /**
     * @param mixed $type
     * @param mixed $asPageId
     */
    public function __construct(string $requesterId, string $postId, $type, ?string $asPageId) {
        $this->requesterId = $requesterId;
        $this->postId = $postId;
        $this->type = $type;
        $this->asPageId = $asPageId;
    }

}
