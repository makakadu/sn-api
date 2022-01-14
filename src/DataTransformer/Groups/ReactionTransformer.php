<?php
declare(strict_types=1);
namespace App\DataTransformer\Groups;

use App\DTO\Groups\GroupReactionDTO;
use App\Domain\Model\Groups\GroupReaction;

class ReactionTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transform(GroupReaction $reaction): GroupReactionDTO {
        $creator = $reaction->creator();
        $onBehalfOfGroup = $reaction->onBehalfOfGroup();
        
        return new GroupReactionDTO(
            $reaction->id(),
            $reaction->getReactionType(),
            $onBehalfOfGroup ? null : $this->creatorToDTO($creator),
            $onBehalfOfGroup,
            $this->creationTimeToTimestamp($reaction->createdAt())
        );
    }
}