<?php
declare(strict_types=1);
namespace App\Application\Users\Settings\GetSettings;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;

class GetSettings implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    function __construct(UserRepository $users) {
        $this->users = $users;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $user = $this->findUserOrFail($request->userId, true, null);
        
        if(!$requester->equals($user)) {
            throw new \App\Application\Exceptions\ForbiddenException(\App\Application\Errors::NO_RIGHTS, "No rigths");
        }
        $settings = $user->settings();
        
        return new GetSettingsResponse($settings->language(), $settings->theme());
    }
}
