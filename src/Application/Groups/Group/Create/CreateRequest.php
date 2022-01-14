<?php
declare(strict_types=1);
namespace App\Application\Groups\Group\Create;

use App\Application\BaseRequest;

class CreateRequest implements BaseRequest {
    public string $requesterId;
    /** @var mixed $name */
    public $name;
    /** @var mixed $private */
    public $private;
    /** @var mixed $visible */
    public $visible;
            
    /**
     * @param mixed $name
     * @param mixed $private
     * @param mixed $visible
     */
    function __construct(string $requesterId, $name, $private, $visible) {
        $this->requesterId = $requesterId;
        $this->name = $name;
        $this->private = $private;
        $this->visible = $visible;
    }


}
