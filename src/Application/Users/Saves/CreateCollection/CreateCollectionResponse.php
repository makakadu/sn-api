<?php //
declare(strict_types=1);
namespace App\Application\Users\Saves\CreateCollection;

use App\Application\BaseResponse;

class CreateCollectionResponse implements BaseResponse {
    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
