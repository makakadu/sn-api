<?php
declare(strict_types=1);
namespace App\DataTransformer\Pages;

use App\Domain\Model\Pages\Photos\AlbumPhoto\AlbumPhoto;
use App\DTO\Pages\PageAlbumPhotoDTO;
use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Common\Reaction;

class AlbumPhotoTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transform(AlbumPhoto $photo, int $commentsCount, string $commentsType, string $commentsOrder): PageAlbumPhotoDTO {
        /** @var Collection<string, Reaction> $reactions */
        $reactions = $photo->reactions();
        $album = $photo->album();
        
        return new PageAlbumPhotoDTO(
            $photo->id(),
            $photo->versions(),
            $photo->description(),
            $album->id(),
            $album->name(),
            $this->creatorToDTO($photo->creator()),
            $this->pageToSmallDTO($photo->owningPage()),
            $photo->onBehalfOfPage(),
            $photo->createdAt()->getTimestamp() * 1000,
            $this->reactionsToDTO($photo->reactions(), 20),
            $this->prepareReactionsCount($reactions),
            $this->commentsToDTO($photo->comments(), $commentsCount, $commentsType, $commentsOrder)
        );
    }
    
}