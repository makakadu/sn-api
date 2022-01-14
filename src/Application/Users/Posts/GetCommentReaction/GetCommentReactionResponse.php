<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetCommentReaction;

use App\Application\BaseResponse;
use App\DTO\Users\ReactionDTO;

class GetCommentReactionResponse implements BaseResponse {
    public ReactionDTO $reaction;
    
    public function __construct(ReactionDTO $reaction) {
        $this->reaction = $reaction;
    }

}
