<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetCommentReactions;

use App\Application\BaseResponse;
use App\Domain\Model\Users\Post\Comments\Comment;

class GetCommentReactionsResponse implements BaseResponse {
    public array $reactions = [];
    public array $reactionsCount = [];
    
    public function __construct(array $reactions, array $reactionsCount) {
        $this->reactions = $reactions;
        $this->reactionsCount = $reactionsCount;
    }

}
