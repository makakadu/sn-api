<?php
declare(strict_types=1);
namespace App\Application\Users\Ban\Patch;

use App\Application\BaseRequest;

class PatchRequest implements BaseRequest {
    public string $requesterId;
    public string $postId;
    /** @var mixed $property */
    public $property;
    /** @var mixed $value */
    public $value;
    
    /** @param mixed $payload */
    function __construct(string $requesterId, string $postId, $property, $value) {
        $this->requesterId = $requesterId;
        $this->postId = $postId;
        $this->property = $property;
        $this->value = $value;
    }
}
