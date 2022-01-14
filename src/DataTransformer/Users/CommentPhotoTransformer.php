<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;

use App\Domain\Model\Users\Comments\Photo\Photo;
use App\DTO\Users\CommentPhotoDTO;

class CommentPhotoTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transform(Photo $photo): CommentPhotoDTO {

        return new CommentPhotoDTO(
            $photo->id(),
            $photo->versions(),
            $this->creatorToDTO($photo->owner()),
            $this->creationTimeToTimestamp($photo->createdAt()),
            $photo->commentId()
        );
    }
    
}