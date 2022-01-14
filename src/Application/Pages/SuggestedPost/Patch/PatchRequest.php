<?php
declare(strict_types=1);
namespace App\Application\Pages\SuggestedPost\Patch;

use App\Application\BaseRequest;

class PatchRequest implements BaseRequest {
    public string $requesterId;
    public string $postId;
    /** @var mixed $payload */
    public $payload;
    
    /** @param mixed $payload */
    function __construct(string $requesterId, string $postId, $payload) {
        $this->requesterId = $requesterId;
        $this->postId = $postId;
        $this->payload = $payload;
    }
}
