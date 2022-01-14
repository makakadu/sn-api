<?php
declare(strict_types=1);
namespace App\DataTransformer\Groups;

use App\Domain\Model\Groups\Videos\Video;
use App\DTO\Groups\GroupVideoDTO;
use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Common\Reaction;

class VideoTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transform(Video $video, int $commentsCount, string $commentsType, string $commentsOrder): GroupVideoDTO {

        /** @var Collection<string, Reaction> $reactions */
        $reactions = $video->reactions();
        
        return new \App\DTO\Groups\GroupVideoDTO(
            $video->id(),
            $video->previewMedium(),
            $video->name(),
            $video->description(),
            $video->link(),
            $this->creatorToDTO($video->creator()),
            $this->groupToSmallDTO($video->owningGroup()),
            $video->onBehalfOfGroup(),
            $video->createdAt()->getTimestamp() * 1000,
            $this->reactionsToDTO($video->reactions(), 20),
            $this->prepareReactionsCount($reactions),
            $this->commentsToDTO($video->comments(), $commentsCount, $commentsType, $commentsOrder)
        );
    }
    
}