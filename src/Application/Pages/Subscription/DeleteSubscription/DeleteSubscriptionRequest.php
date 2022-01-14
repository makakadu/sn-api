<?php
declare(strict_types=1);
namespace App\Application\Pages\Subscription\UpdateSubscription;

use App\Application\BaseRequest;

class CreateConnectionRequest implements BaseRequest {
    public $requesterId;
    public $requesteeId;
    
    public function __construct($requesterId, $requesteeId) {
        $this->requesterId = $requesterId;
        $this->requesteeId = $requesteeId;
    }
}
