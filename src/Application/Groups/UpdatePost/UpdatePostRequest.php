<?php
declare(strict_types=1);
namespace App\Application\Groups\UpdatePost;

use App\Application\BaseRequest;

class UpdatePostRequest implements BaseRequest {
    public $requesterId;
    public $postId;
    public $payload;
    
    function __construct($requesterId, $postId, $payload) {
        $this->requesterId = $requesterId;
        $this->postId = $postId;
        $this->payload = $payload;
    }
}
