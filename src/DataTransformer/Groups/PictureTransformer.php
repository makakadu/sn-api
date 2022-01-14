<?php
declare(strict_types=1);
namespace App\DataTransformer\Groups;

use App\Domain\Model\Groups\Photos\GroupPicture\GroupPicture;
use App\DTO\Groups\GroupPictureDTO;
use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Common\Reaction;

class PictureTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    
    function transform(GroupPicture $picture, int $commentsCount, string $commentsType, string $commentsOrder): GroupPictureDTO {
        /** @var Collection<string, Reaction> $reactions */
        $reactions = $picture->reactions();
        
        return new GroupPictureDTO(
            $picture->id(),
            $picture->versions(),
            $picture->description(),
            null,
            $this->groupToSmallDTO($picture->owningGroup()),
            $picture->createdAt()->getTimestamp() * 1000,
            $this->reactionsToDTO($picture->reactions(), 20),
            $this->prepareReactionsCount($reactions),
            $this->commentsToDTO(
                $picture->comments(), $commentsCount, $commentsType, $commentsOrder
            )
        );
    }
    
}