<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;

use App\Domain\Model\Users\Photos\ProfilePicture\ProfilePicture;
use App\DTO\Users\PictureDTO;
use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Common\Reaction;
use App\Domain\Model\Users\User\User;

class PictureTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
//    function transform(ProfilePicture $picture, int $commentsCount, string $commentsType, string $commentsOrder): PictureDTO {
//        /** @var Collection<string, Reaction> $reactions */
//        $reactions = $picture->reactions();
//        
//        return new PictureDTO(
//            $picture->id(),
//            $picture->versions(),
//            $picture->description(),
//            $this->creatorToDTO($picture->owner()),
//            $picture->createdAt()->getTimestamp() * 1000,
//            $this->reactionsToDTO($picture->reactions(), 20),
//            $this->prepareReactionsCount($reactions),
//            $this->commentsToDTO(
//                $picture->comments(),
//                $commentsCount,
//                $commentsType,
//                $commentsOrder
//            )
//        );
//    }
//    
    function transform(ProfilePicture $picture): PictureDTO {
        
        return new PictureDTO(
            $picture->id(),
            $picture->versions(),
            $this->creatorToDTO($picture->owner()),
            $this->creationTimeToTimestamp($picture->createdAt())
        );
    }
    
}