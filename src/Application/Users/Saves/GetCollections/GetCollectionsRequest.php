<?php
declare(strict_types=1);
namespace App\Application\Users\Saves\GetCollections;

use App\Application\BaseRequest;

class GetCollectionsRequest implements BaseRequest {
    public ?string $requesterId;
    /** @var mixed $ownerId */
    public $ownerId;
    /** @var mixed $offsetId */
    public $offsetId;
    /** @var mixed $count */
    public $count;
    /** @var mixed $order */
    public $order;
    
    /**
     * @param mixed $ownerId
     * @param mixed $offsetId
     * @param mixed $count
     * @param mixed $order
     */
    public function __construct(?string $requesterId, $ownerId, $offsetId, $count, $order) {
        $this->requesterId = $requesterId;
        $this->ownerId = $ownerId;
        $this->offsetId = $offsetId;
        $this->count = $count;
        $this->order = $order;
    }


}
