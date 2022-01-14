<?php
declare(strict_types=1);
namespace App\Application\Users\Settings\UpdateComplexPrivacySetting;

use App\Application\BaseResponse;

class UpdateComplexPrivacySettingResponse implements BaseResponse {

    public string $responseMessage;
    
    function __construct(string $message) {
        $this->responseMessage = $message;
    }
}
