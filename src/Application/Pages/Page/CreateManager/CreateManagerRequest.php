<?php
declare(strict_types=1);
namespace App\Application\Pages\Page\CreateManager;

use App\Application\BaseRequest;

class CreateManagerRequest implements BaseRequest {
    public string $requesterId;
    public string $pageId;
    /** @var mixed $userId */
    public $userId;
    /** @var mixed $position */
    public $position;
    /** @var mixed $showInContacts */
    public $showInContacts;
    /** @var mixed $allowExternalActivity */
    public $allowExternalActivity;

    /**
     * @param mixed $userId
     * @param mixed $position
     * @param mixed $showInContacts
     * @param mixed $allowExternalActivity
     */
    public function __construct(string $requesterId,  string $pageId, $userId, $position, $showInContacts, $allowExternalActivity) {
        $this->requesterId = $requesterId;
        $this->pageId = $pageId;
        $this->userId = $userId;
        $this->position = $position;
        $this->showInContacts = $showInContacts;
        $this->allowExternalActivity = $allowExternalActivity;
    }


}
