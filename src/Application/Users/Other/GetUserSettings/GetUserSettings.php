<?php
declare(strict_types=1);
namespace App\Application\Users\GetUserSettings;

use App\Application\ApplicationService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;

class GetUserSettings {
    
    public function execute(BaseRequest $request): BaseResponse {
        //$requester = $this->findAuthenticatedUser($this->getCurrentUserId());
//
//        $requested = $this->findUserOrFail($requestedId);
//        $settings = $requested->profileSettings();
//        
//        $settingsArr['language'] = $settings->getLanguage();
//        $settingsArr['themeIsDark'] = $settings->themeIsDark();
                
        return new GetUserSettingsResponse([]);
    }
}
