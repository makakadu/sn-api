<?php
declare(strict_types=1);
namespace App\Application\Users\ProfilePicture\Get;

use App\Application\BaseRequest;

class GetRequest implements BaseRequest {
    public ?string $requesterId;
    public string $pictureId;
    
    public function __construct(?string $requesterId, string $pictureId) {
        $this->requesterId = $requesterId;
        $this->pictureId = $pictureId;
    }

}
