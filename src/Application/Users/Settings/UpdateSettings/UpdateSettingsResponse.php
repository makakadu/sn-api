<?php
declare(strict_types=1);
namespace App\Application\Users\Settings\UpdateSettings;

use App\Application\BaseResponse;

class UpdateSettingsResponse implements BaseResponse {

    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
