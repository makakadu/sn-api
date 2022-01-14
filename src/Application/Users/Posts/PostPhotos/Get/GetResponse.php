<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\PostPhotos\Get;

use App\Application\BaseResponse;
use App\DTO\Users\PostPhotoDTO;

class GetResponse implements BaseResponse {
    
    public PostPhotoDTO $photo;

    public function __construct(PostPhotoDTO $photo) {
        $this->photo = $photo;
    }
}