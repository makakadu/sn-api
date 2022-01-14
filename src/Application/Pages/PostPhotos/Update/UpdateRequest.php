<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\PostPhotos\Update;

use App\Application\BaseRequest;

class UpdateRequest implements BaseRequest {
    public string $requesterId;
    public string $photoId;
    /** @var array<mixed> $payload */
    public $payload;
    
    /**
     * @param array<mixed> $payload
     */
    function __construct(string $requesterId, string $photoId, $payload) {
        $this->requesterId = $requesterId;
        $this->photoId = $photoId;
        $this->payload = $payload;
    }


}
