<?php
declare(strict_types=1);
namespace App\Application\Users\Settings\UpdateComplexPrivacySetting;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\ApplicationService;
use App\Application\AppServiceTrait;
use App\Domain\Model\Users\User\UserRepository;

class UpdateComplexPrivacySetting implements ApplicationService {
    use AppServiceTrait;
    use \App\Application\Users\PrivacyAppServiceTrait;
    
    function __construct(UserRepository $users) {
        $this->users = $users;
    }

        public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $this->failIfIncorrectPrivacyStructure("privacy_data", $request->data);
        // $request->name можно не проверять, оно должно быть правильным, потому что его передаёт НЕ клиент
        $requester->editComplexPrivacySetting($request->name, $request->data);
        //exit();
        
        return new UpdateComplexPrivacySettingResponse('Setting updated');
    }
}