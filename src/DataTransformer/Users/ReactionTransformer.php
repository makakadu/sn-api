<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;

use App\DTO\Users\ReactionDTO;
use App\Domain\Model\Users\ProfileReaction;

class ReactionTransformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transform(ProfileReaction $reaction): ReactionDTO {
        $creator = $reaction->creator();
        //$asPage = $reaction->onBehalfOfPage();
        
        return new ReactionDTO(
            $reaction->id(),
            $reaction->getReactionType(),
            $this->creatorToDTO($creator),
            //$this->pageToSmallDTO($asPage) : null,
            $this->creationTimeToTimestamp($reaction->createdAt())
        );
    }
}