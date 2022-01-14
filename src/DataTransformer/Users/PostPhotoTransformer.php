<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;

use App\Domain\Model\Users\Post\Photo\Photo;
use App\DTO\Users\PostPhotoDTO;

class PostPhotoTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transform(Photo $photo): PostPhotoDTO {

        return new PostPhotoDTO(
            $photo->id(),
            $photo->versions(),
            $this->creatorToDTO($photo->owner()),
            $this->creationTimeToTimestamp($photo->createdAt()),
            $photo->post() ? $photo->post()->id() : null
        );
    }
    
}