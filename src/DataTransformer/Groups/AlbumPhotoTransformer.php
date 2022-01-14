<?php
declare(strict_types=1);
namespace App\DataTransformer\Groups;

use App\Domain\Model\Groups\Photos\AlbumPhoto\AlbumPhoto;
use App\DTO\Groups\GroupAlbumPhotoDTO;
use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Common\Reaction;

class AlbumPhotoTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transform(AlbumPhoto $photo, int $commentsCount, string $commentsType, string $commentsOrder): GroupAlbumPhotoDTO {
        /** @var Collection<string, Reaction> $reactions */
        $reactions = $photo->reactions();
        $album = $photo->album();
        
        return new GroupAlbumPhotoDTO(
            $photo->id(),
            $photo->versions(),
            $photo->description(),
            $album->id(),
            $album->name(),
            $this->creatorToDTO($photo->creator()),
            $this->groupToSmallDTO($photo->owningGroup()),
            $photo->onBehalfOfGroup(),
            $photo->createdAt()->getTimestamp() * 1000,
            $this->reactionsToDTO($photo->reactions(), 20),
            $this->prepareReactionsCount($reactions),
            $this->commentsToDTO($photo->comments(), $commentsCount, $commentsType, $commentsOrder)
        );
    }
    
}