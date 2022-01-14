<?php
declare(strict_types=1);
namespace App\Application\Users\Saves\GetItems;

use App\Application\BaseRequest;

class GetItemsRequest implements BaseRequest {
    public ?string $requesterId;
    public string $collectionId;
    
    function __construct(?string $requesterId, string $itemId) {
        $this->requesterId = $requesterId;
        $this->itemId = $itemId;
    }

}
