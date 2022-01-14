<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetComment;

use App\Application\BaseResponse;
use App\DTO\Users\UserPostDTO;
use App\DTO\Users\PostCommentDTO;

class GetCommentResponse implements BaseResponse {
    
    public PostCommentDTO $comment;

    public function __construct(PostCommentDTO $commentDTO) {
        $this->comment = $commentDTO;
    }
}