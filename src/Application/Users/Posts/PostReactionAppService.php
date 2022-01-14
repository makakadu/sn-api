<?php
declare(strict_types=1);
namespace App\Application\Users\Posts;

use App\Application\Exceptions\NotExistException;
use App\Domain\Model\Users\Post\Reaction;
use App\Domain\Model\Users\Post\ReactionRepository as PostReactionRepository;

trait PostReactionAppService {
    use \App\Application\Common\ReactionAppServiceTrait;
    
    private PostReactionRepository $reactions;

    protected function findReactionOrFail(string $reactionId): Reaction {
        $reaction = $this->reactions->getById($reactionId);
        // Возможно Reaction будет содержать ссылку на владельца
        if(!$reaction || $reaction->post()->creator()->isDeleted()) {
            throw new NotExistException("Reaction $reactionId not found");
        }
        return $reaction;
    }
    
}