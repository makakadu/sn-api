<?php
declare(strict_types=1);
namespace App\Application\Users\Saves\CreateCollection;

use App\Application\BaseRequest;

class CreateCollectionRequest implements BaseRequest {
    public string $requesterId;
    /** @var mixed $name */
    public $name;
    /** @var mixed $description */
    public $description;
    /** @var mixed $whoCanSee */
    public $whoCanSee;
    
    /**
    * @param mixed $name
    * @param mixed $description
    * @param mixed $whoCanSee
    **/
    function __construct(string $requesterId, $name, $description, $whoCanSee) {
        $this->requesterId = $requesterId;
        $this->name = $name;
        $this->description = $description;
        $this->whoCanSee = $whoCanSee;
    }
}
