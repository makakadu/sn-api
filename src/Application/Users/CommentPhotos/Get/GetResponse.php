<?php
declare(strict_types=1);
namespace App\Application\Users\CommentPhotos\Get;

use App\Application\BaseResponse;
use App\DTO\Users\CommentPhotoDTO;

class GetResponse implements BaseResponse {
    
    public CommentPhotoDTO $photo;

    public function __construct(CommentPhotoDTO $photo) {
        $this->photo = $photo;
    }
}