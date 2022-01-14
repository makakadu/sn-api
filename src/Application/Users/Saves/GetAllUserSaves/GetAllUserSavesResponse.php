<?php
declare(strict_types=1);
namespace App\Application\Users\Saves\GetItems;

use App\Domain\Model\Users\SavesCollection\SavedItem;
use App\DTO\Users\Saves\SavedDTO;

class GetItemsResponse implements \App\Application\BaseResponse {
    /** @var array<int, ItemDTO> $items */
    public array $items;
    
    /** @param array<int, ItemDTO> $items */
    public function __construct(array $items) {
        $this->items = $items;
    }
}
