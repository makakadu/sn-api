<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;

use App\Domain\Model\Users\Photos\Cover\Cover;
use App\DTO\Users\CoverDTO;

class CoverTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transform(Cover $cover): CoverDTO {
        
        return new CoverDTO(
            $cover->id(),
            $cover->versions(),
            $this->creatorToDTO($cover->owner()),
            $this->creationTimeToTimestamp($cover->createdAt())
        );
    }
    
}