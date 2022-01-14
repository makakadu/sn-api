<?php
declare(strict_types=1);
namespace App\Application\Users\Posts;

use App\Application\Exceptions\NotExistException;
use App\Domain\Model\Users\Post\Comments\Reaction;
use App\Domain\Model\Users\Post\Comments\ReactionRepository;

trait PostCommentReactionAppService {
    use \App\Application\Common\ReactionAppServiceTrait;
    
    protected ReactionRepository $reactions;
    
    protected function findReactionOrFail(string $reactionId): Reaction {
        $reaction = $this->reactions->getById($reactionId);
        
        if(!$reaction || $reaction->comment()->owner()->isDeleted()) {
            throw new NotExistException("Reaction ".$reactionId." not found");
        }
        return $reaction;
    }
    
}