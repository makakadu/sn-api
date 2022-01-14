<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\DeleteComment;

use App\Application\BaseResponse;

class DeleteCommentResponse implements BaseResponse {
    private string $message;
            
    function __construct(string $message) {
        $this->message = $message;
    }
}
