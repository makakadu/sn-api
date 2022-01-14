<?php
declare(strict_types=1);
namespace App\Application\Users\Settings\UpdatePrivacy;

use App\Application\BaseResponse;

class UpdatePrivacyResponse implements BaseResponse {

    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
