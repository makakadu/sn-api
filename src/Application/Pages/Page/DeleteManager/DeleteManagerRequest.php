<?php
declare(strict_types=1);
namespace App\Application\Pages\Page\DeleteManager;

use App\Application\BaseRequest;

class DeleteManagerRequest implements BaseRequest {
    public string $requesterId;
    public string $pageId;
    /** @var mixed $userId */
    public $userId;
    /** @var mixed $level */
    public $level;
    /** @var mixed $showInContacts */
    public $showInContacts;

    /**
     * @param mixed $userId
     * @param mixed $level
     * @param mixed $showInContacts
     */
    public function __construct(string $requesterId,  string $pageId, $userId, $level, $showInContacts) {
        $this->requesterId = $requesterId;
        $this->pageId = $pageId;
        $this->userId = $userId;
        $this->level = $level;
        $this->showInContacts = $showInContacts;
    }


}
