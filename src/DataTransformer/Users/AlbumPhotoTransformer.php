<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;

use App\Domain\Model\Users\Photos\AlbumPhoto\AlbumPhoto;
use App\DTO\Users\UserAlbumPhotoDTO;
use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Common\Reaction;
use App\Domain\Model\Users\User\User;

class AlbumPhotoTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transform(AlbumPhoto $photo, int $commentsCount, string $commentsType, string $commentsOrder): UserAlbumPhotoDTO {
        /** @var Collection<string, Reaction> $reactions */
        $reactions = $photo->reactions();
        
        $album = $photo->album();
        
        return new UserAlbumPhotoDTO(
            $photo->id(),
            $photo->versions(),
            $photo->description(),
            $album->id(),
            $album->name(),
            $this->creatorToDTO($photo->owner()),
            $photo->createdAt()->getTimestamp() * 1000,
            $this->reactionsToDTO($photo->reactions(), 20),
            $this->prepareReactionsCount($reactions),
            $this->commentsToDTO($photo->comments(), $commentsCount, $commentsType, $commentsOrder)
        );
    }
    
}