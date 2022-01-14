<?php
declare(strict_types=1);
namespace App\DataTransformer\Pages;

use App\DTO\Pages\PageReactionDTO;
use App\Domain\Model\Pages\PageReaction;

class ReactionTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transform(PageReaction $reaction): PageReactionDTO {
        $creator = $reaction->creator();
        $onBehalfOfPage = $reaction->onBehalfOfPage();
        
        return new PageReactionDTO(
            $reaction->id(),
            $reaction->getReactionType(),
            $onBehalfOfPage ? null : $this->creatorToDTO($creator),
            $onBehalfOfPage,
            $this->creationTimeToTimestamp($reaction->createdAt())
        );
    }
}