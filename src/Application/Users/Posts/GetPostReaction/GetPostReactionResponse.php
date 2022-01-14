<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetPostReaction;

use App\Application\BaseResponse;
use App\Domain\Model\Users\Post\Reaction;
use App\DTO\Users\ReactionDTO;

class GetPostReactionResponse implements BaseResponse {
    public ReactionDTO $reaction;
    
    public function __construct(ReactionDTO $reaction) {
        $this->reaction = $reaction;
    }

}
