<?php //
declare(strict_types=1);
namespace App\Application\Groups\Group\Create;

use App\Application\BaseResponse;

class CreateResponse implements BaseResponse {
    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
