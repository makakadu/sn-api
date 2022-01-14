<?php
declare(strict_types=1);
namespace App\Application\Groups\Membership\Accept;

use App\Application\BaseRequest;

class AcceptRequest implements BaseRequest {
    public string $requesterId;
    /** @var mixed $membershipId; */
    public $membershipId;
    
    /** 
     * @param mixed $membershipId
     */
    public function __construct(string $requesterId, $membershipId) {
        $this->requesterId = $requesterId;
        $this->membershipId = $membershipId;
    }
}
