<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\Delete;

use App\Application\BaseRequest;

class DeleteRequest implements BaseRequest {
    public string $requesterId;
    public string $postId;
    
    function __construct(string $requesterId, string $postId) {
        $this->requesterId = $requesterId;
        $this->postId = $postId;
    }

}
