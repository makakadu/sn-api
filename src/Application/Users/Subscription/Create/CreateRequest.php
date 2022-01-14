<?php
declare(strict_types=1);
namespace App\Application\Users\Subscription\Create;

use App\Application\BaseRequest;

class CreateRequest implements BaseRequest {
    public string $requesterId;
    /** @var mixed $requesteeId */
    public $requesteeId;
    
    /**
    * @param mixed $requesteeId
    **/
    public function __construct(string $requesterId, $requesteeId) {
        $this->requesterId = $requesterId;
        $this->requesteeId = $requesteeId;
    }
}
