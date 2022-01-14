<?php
declare(strict_types=1);
namespace App\Application\Users\Saves\CreateItem;

use App\Application\BaseRequest;

class CreateItemRequest implements BaseRequest {
    public string $requesterId;
    public string $collectionId;
    /** @var mixed $type */
    public $type;
    /** @var mixed $id */
    public $id;

     /**
      * @param mixed $type
      * @param mixed $id
      */
    public function __construct(string $requesterId, string $collectionId, $type, $id) {
        $this->requesterId = $requesterId;
        $this->collectionId = $collectionId;
        $this->type = $type;
        $this->id = $id;
    }

}
