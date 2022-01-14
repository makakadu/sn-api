<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetReactions;

use App\Application\BaseResponse;
use App\Domain\Model\Users\Post\Reaction;
use App\DTO\Users\ReactionDTO;

class GetReactionsResponse implements BaseResponse {
    
    public array $reactions = [];
    public array $reactionsCount = [];
    
    public function __construct(array $reactions, array $reactionsCount) {
        $this->reactions = $reactions;
        $this->reactionsCount = $reactionsCount;
    }

}
