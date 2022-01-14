<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\PostPhotos\Get;

use App\Application\BaseRequest;

class GetRequest implements BaseRequest {
    public ?string $requesterId;
    public string $photoId;
    
    public function __construct(?string $requesterId, string $photoId) {
        $this->requesterId = $requesterId;
        $this->photoId = $photoId;
    }

}
