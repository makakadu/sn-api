<?php
declare(strict_types=1);
namespace App\Application\Users\Saves\CreateCollection;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\ApplicationService;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\PrivacyService\PrivacyService;

class CreateCollection implements ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private PrivacyService $privacyService;
    
    public function __construct(UserRepository $users, PrivacyService $privacyService) {
        $this->users = $users;
        $this->privacyService = $privacyService;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);

        $whoCanSee = $request->whoCanSee;
        $this->privacyService->checkPrivacyData('who_can_see', $whoCanSee);
        $requester->createSavesCollection(
            $request->name,
            $request->description,
            [
                'access_level' => $whoCanSee['access_level'],
                'lists' => $this->privacyService->prepareLists($whoCanSee['lists'])
            ]
        );

        return new CreateCollectionResponse("OK");
    }
}