<?php
declare(strict_types=1);
namespace App\Application\Groups\Membership\Delete;

use App\Application\BaseRequest;

class DeleteRequest implements BaseRequest {
    public string $requesterId;
    /** @var mixed $groupId */
    public $groupId;
    /** @var mixed $membershipId; */
    public $membershipId;
    
    /** 
     * @param mixed $groupId
     * @param mixed $membershipId
     */
    public function __construct(string $requesterId, $groupId, $membershipId) {
        $this->requesterId = $requesterId;
        $this->groupId = $groupId;
        $this->membershipId = $membershipId;
    }
}
