<?php
declare(strict_types=1);
namespace App\Application\Pages\Page\Create;

use App\Application\BaseRequest;

class CreateRequest implements BaseRequest {
    public string $requesterId;
    /** @var mixed $name */
    public $name;
    /** @var mixed $description */
    public $description;
    /** @var mixed $subject */
    public $subject;
    
    /**
     * @param mixed $name
     * @param mixed $description
     * @param mixed $subject
     */
    public function __construct(string $requesterId, $name, $description, $subject) {
        $this->requesterId = $requesterId;
        $this->name = $name;
        $this->description = $description;
        $this->subject = $subject;
    }

}
