<?php
declare(strict_types=1);
namespace App\Application\Users\Subscription\Update;

use App\Application\BaseRequest;

class UpdateRequest implements BaseRequest {
    public string $requesterId;
    public string $subscriptionId;
    /** @var mixed $property */
    public $property;
    /** @var mixed $value */
    public $value;
    
    /**
    * @param mixed $property
    * @param mixed $value
    **/
    function __construct(string $requesterId, string $subscriptionId, $property, $value) {
        $this->requesterId = $requesterId;
        $this->subscriptionId = $subscriptionId;
        $this->property = $property;
        $this->value = $value;
    }

}
