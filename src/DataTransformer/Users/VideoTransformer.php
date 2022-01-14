<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;

use App\Domain\Model\Users\Videos\Video;
use App\DTO\Users\UserVideoDTO;
use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Common\Reaction;

class VideoTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transform(Video $video, int $commentsCount, string $commentsType, string $commentsOrder): UserVideoDTO {

        /** @var Collection<string, Reaction> $reactions */
        $reactions = $video->reactions();
        
        return new UserVideoDTO(
            $video->id(),
            $video->previewMedium(),
            $video->name(),
            $video->description(),
            $video->link(),
            $this->creatorToDTO($video->creator()),
            $video->createdAt()->getTimestamp() * 1000,
            $this->reactionsToDTO($video->reactions(), 20),
            $this->prepareReactionsCount($reactions),
            $this->commentsToDTO($video->comments(), $commentsCount, $commentsType, $commentsOrder),
            $video->comments()->count(),
        );
    }
    
}