<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\CreateComment;

use App\Application\BaseResponse;

class CreateCommentResponse implements BaseResponse {
    public string $id;
    
    function __construct(string $commentId) {
        $this->id = $commentId;
    }
}
