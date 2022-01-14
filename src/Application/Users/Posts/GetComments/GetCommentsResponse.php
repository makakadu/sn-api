<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetComments;

use App\Application\BaseResponse;
use App\Domain\Model\Users\Post\Comments\Comment;
use App\DTO\Users\PostCommentDTO;

class GetCommentsResponse implements BaseResponse {
    public int $allCommentsCount;
    /** @var array<int, PostCommentDTO> $comments */
    public array $items = [];
    
    /** @param array<int, PostCommentDTO> $comments */
    public function __construct(array $comments, int $allCommentsCount) {
        $this->items = $comments;
        $this->allCommentsCount = $allCommentsCount;
    }
}
