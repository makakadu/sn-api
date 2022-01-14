<?php
declare(strict_types=1);
namespace App\Application\Groups\Group\CreateManager;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Groups\Group\GroupRepository;

class CreateManager implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Groups\GroupAppServiceTrait;
    
    function __construct(
        UserRepository $users,
        GroupRepository $groups
    ) {
        $this->groups = $groups;
        $this->users = $users;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        //$this->validateRequest($request);
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $group = $this->findGroupOrFail($request->groupId, true, null);
        $user = $this->findUserOrFail($request->userId, false, null);
        
        $group->addManager(
            $requester, 
            $user,
            $request->position, 
            (bool)$request->showInContacts
        );
        return new CreateManagerResponse("OK");
    }
    
//    private function validateRequest(CreateRequest $request): void {
//        
//    }
}        

