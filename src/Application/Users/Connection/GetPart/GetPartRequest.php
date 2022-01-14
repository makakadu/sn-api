<?php
declare(strict_types=1);
namespace App\Application\Users\Connection\GetPart;

use App\Application\BaseRequest;

class GetPartRequest implements BaseRequest {
    public string $requesterId;
    public string $userId;
    
    /** @var mixed $cursor */
    public $cursor;
    /** @var mixed $count */
    public $count;
    /** @var mixed $hideAccepted */
    public $hideAccepted;
    
    /** @var mixed $hidePending */
    public $hidePending;
    
    /** @var mixed $type */
    public $type;
    
    /**
     * @param mixed $cursor
     * @param mixed $count
     * @param mixed $hideAccepted
     * @param mixed $hidePending
     * @param mixed $type
     */
    public function __construct(string $requesterId, string $userId, $cursor, $hideAccepted, $hidePending, $count, $type) {
        $this->requesterId = $requesterId;
        $this->userId = $userId;
        $this->cursor = $cursor;
        $this->count = $count;
        $this->hideAccepted = $hideAccepted;
        $this->hidePending = $hidePending;
        $this->type = $type;
    }

}
