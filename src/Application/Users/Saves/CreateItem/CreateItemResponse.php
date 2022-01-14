<?php //
declare(strict_types=1);
namespace App\Application\Users\Saves\CreateItem;

use App\Application\BaseResponse;

class CreateItemResponse implements BaseResponse {
    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
