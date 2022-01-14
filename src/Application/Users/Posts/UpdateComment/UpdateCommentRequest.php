<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\UpdateComment;

use App\Application\BaseRequest;

class UpdateCommentRequest implements BaseRequest {
    public string $requesterId;
    public string $commentId;
    /** @var mixed $property */
    public $property;
    /** @var mixed $value */
    public $value;
    
    /**
     * @param mixed $property
     * @param mixed $value
     */
    function __construct(string $requesterId, string $commentId, $property, $value) {
        $this->requesterId = $requesterId;
        $this->commentId = $commentId;
        $this->property = $property;
        $this->value = $value;
    }

}
