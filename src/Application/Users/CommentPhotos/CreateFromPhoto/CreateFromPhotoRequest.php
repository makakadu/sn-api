<?php
declare(strict_types=1);
namespace App\Application\Users\CommentPhotos\CreateFromPhoto;

use App\Application\BaseRequest;
use Psr\Http\Message\UploadedFileInterface;

class CreateFromPhotoRequest implements BaseRequest {
    public string $requesterId;
    public UploadedFileInterface $uploadedPhoto;
    
    function __construct(string $requesterId,UploadedFileInterface $uploadedPhoto) {
        $this->requesterId = $requesterId;
        $this->uploadedPhoto = $uploadedPhoto;
    }

}
