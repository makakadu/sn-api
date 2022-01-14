<?php
declare(strict_types=1);
namespace App\DataTransformer\Pages;

use App\Domain\Model\Pages\Photos\PagePicture\PagePicture;
use App\DTO\Pages\PagePictureDTO;
use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Common\Reaction;

class PictureTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    
    function transform(PagePicture $picture, int $commentsCount, string $commentsType, string $commentsOrder): PagePictureDTO {
        /** @var Collection<string, Reaction> $reactions */
        $reactions = $picture->reactions();
        
        return new PagePictureDTO(
            $picture->id(),
            $picture->versions(),
            $picture->description(),
            null,
            $this->pageToSmallDTO($picture->owningPage()),
            $picture->createdAt()->getTimestamp() * 1000,
            $this->reactionsToDTO($picture->reactions(), 20),
            $this->prepareReactionsCount($reactions),
            $this->commentsToDTO(
                $picture->comments(), $commentsCount, $commentsType, $commentsOrder
            )
        );
    }
    
}