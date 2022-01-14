<?php //
declare(strict_types=1);
namespace App\Application\Groups\Posts\Create;

use App\Application\BaseResponse;

class CreateResponse implements BaseResponse {
    public $responseMessage;
    
    function __construct($message) {
        $this->responseMessage = $message;
    }
}
