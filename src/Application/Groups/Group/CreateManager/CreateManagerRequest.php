<?php
declare(strict_types=1);
namespace App\Application\Groups\Group\CreateManager;

use App\Application\BaseRequest;

class CreateManagerRequest implements BaseRequest {
    public string $requesterId;
    public string $groupId;
    /** @var mixed $userId */
    public $userId;
    /** @var mixed $position */
    public $position;
    /** @var mixed $showInContacts */
    public $showInContacts;

    /**
     * @param mixed $userId
     * @param mixed $position
     * @param mixed $showInContacts
     */
    public function __construct(string $requesterId,  string $groupId, $userId, $position, $showInContacts) {
        $this->requesterId = $requesterId;
        $this->groupId = $groupId;
        $this->userId = $userId;
        $this->position = $position;
        $this->showInContacts = $showInContacts;
    }
    
}
