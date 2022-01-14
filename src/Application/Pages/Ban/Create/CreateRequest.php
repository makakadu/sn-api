<?php
declare(strict_types=1);
namespace App\Application\Pages\Ban\Create;

use App\Application\BaseRequest;

class CreateRequest implements BaseRequest {
    public string $requesterId;
    public string $pageId;
    /** @var mixed $userId */
    public $userId;
    /** @var mixed $minutes */
    public $minutes;
    /** @var mixed $reason */
    public $reason;
    /** @var mixed $message */
    public $message;
    

    /**
     * @param mixed $userId
     * @param mixed $minutes
     * @param mixed $reason
     * @param mixed $message
     */
    public function __construct(string $requesterId, string $pageId, $userId, $minutes, $reason, $message) {
        $this->requesterId = $requesterId;
        $this->pageId = $pageId;
        $this->userId = $userId;
        $this->minutes = $minutes;
        $this->reason = $reason;
        $this->message = $message;
    }

}
