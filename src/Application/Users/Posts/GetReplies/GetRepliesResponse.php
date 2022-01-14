<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetReplies;

use App\Application\BaseResponse;
use App\Domain\Model\Users\Post\Comments\Comment;
use App\DTO\Users\PostCommentDTO;

class GetRepliesResponse implements BaseResponse {
    public int $allRepliesCount;
    /** @var array<int, PostCommentDTO> $comments */
    public array $items = [];
    
    /** @param array<int, PostCommentDTO> $comments */
    public function __construct(array $comments, int $allRepliesCount) {
        $this->items = $comments;
        $this->allRepliesCount = $allRepliesCount;
    }
}
