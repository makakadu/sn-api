<?php
declare(strict_types=1);
namespace App\Application\Users\Subscription\GetPart;

use App\Application\BaseRequest;

class GetPartRequest implements BaseRequest {
    public string $requesterId;
    public string $userId;
    
    /** @var mixed $cursor */
    public $cursor;
    /** @var mixed $count */
    public $count;
    
    /**
     * @param mixed $cursor
     * @param mixed $count
     */
    public function __construct(string $requesterId, string $userId, $cursor, $count) {
        $this->requesterId = $requesterId;
        $this->userId = $userId;
        $this->cursor = $cursor;
        $this->count = $count;
    }

}
