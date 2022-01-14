<?php
declare(strict_types=1);
namespace App\Application\Users\Ban\Create;

use App\Application\BaseResponse;

class CreateResponse implements BaseResponse {
    public string $message;
    
    function __construct(string $message) {
        $this->message = $message;
    }

}
