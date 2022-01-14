<?php
declare(strict_types=1);
namespace App\DataTransformer\Pages;

use App\Domain\Model\Pages\Videos\Video;
use App\DTO\Pages\PageVideoDTO;
use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Common\Reaction;

class VideoTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transform(Video $video, int $commentsCount, string $commentsType, string $commentsOrder): PageVideoDTO {

        /** @var Collection<string, Reaction> $reactions */
        $reactions = $video->reactions();
        
        return new PageVideoDTO(
            $video->id(),
            $video->previewMedium(),
            $video->name(),
            $video->description(),
            $video->link(),
            $this->creatorToDTO($video->creator()),
            $this->pageToSmallDTO($video->owningPage()),
            $video->onBehalfOfPage(),
            $video->createdAt()->getTimestamp() * 1000,
            $this->reactionsToDTO($video->reactions(), 20),
            $this->prepareReactionsCount($reactions),
            $this->commentsToDTO($video->comments(), $commentsCount, $commentsType, $commentsOrder)
        );
    }
    
}