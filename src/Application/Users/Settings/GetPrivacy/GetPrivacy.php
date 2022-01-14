<?php
declare(strict_types=1);
namespace App\Application\Users\GetPrivacy;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\ApplicationService_NEW;
use App\Application\Users\PrivacyApplicationService;

class GetPrivacy implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Users\PrivacyAppServiceTrait;
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        //echo $requester->kek2();exit();
        return new GetPrivacyResponse($requester->privacy());
    }
}
