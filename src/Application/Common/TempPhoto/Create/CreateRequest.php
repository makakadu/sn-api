<?php
declare(strict_types=1);
namespace App\Application\Common\TempPhoto\Create;

use App\Application\BaseRequest;
use Psr\Http\Message\UploadedFileInterface;

class CreateRequest implements BaseRequest {
    public string $requesterId;
    public UploadedFileInterface $uploadedPhoto;
    
    function __construct(string $requesterId, UploadedFileInterface $uploadedPhoto) {
        $this->requesterId = $requesterId;
        $this->uploadedPhoto = $uploadedPhoto;
    }

}
