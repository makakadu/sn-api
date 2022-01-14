<?php
declare(strict_types=1);
namespace App\Application\Users\Saves\GetItems;

use App\Application\BaseRequest;

class GetItemsRequest implements BaseRequest {
    public ?string $requesterId;
    public string $collectionId;
    /** @var mixed $offsetId */
    public $offsetId;
    /** @var mixed $count */
    public $count;
    /** @var mixed $order */
    public $types;
    /** @var mixed $commentsOrder */
    public $commentsOrder;
    /** @var mixed $commentsType */
    public $commentsType;
    /** @var mixed $commentsCount */
    public $commentsCount;

    /**
     * 
     * @param string|null $requesterId
     * @param string $collectionId
     * @param mixed $offsetId
     * @param mixed $count
     * @param mixed $order
     * @param mixed $commentsOrder
     * @param mixed $commentsType
     * @param mixed $commentsCount
     */
    public function __construct(
        ?string $requesterId, string $collectionId, $offsetId, $count,
        $order, $commentsOrder, $commentsType, $commentsCount
    ) {
        $this->requesterId = $requesterId;
        $this->collectionId = $collectionId;
        $this->offsetId = $offsetId;
        $this->count = $count;
        $this->order = $order;
        $this->commentsOrder = $commentsOrder;
        $this->commentsType = $commentsType;
        $this->commentsCount = $commentsCount;
    }


}
