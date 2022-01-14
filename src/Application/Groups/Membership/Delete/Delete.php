<?php
declare(strict_types=1);
namespace App\Application\Groups\Membership\Delete;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Groups\Group\GroupRepository;

class Delete implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Groups\GroupAppServiceTrait;
    
    public function __construct(GroupRepository $groups, UserRepository $users) {
        $this->users = $users;
        $this->groups = $groups;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);

        $group = $this->findGroupOrFail($request->groupId, true);
        $group->deleteMembership($requester, $request->membershipId);
        
        return new DeleteResponse('ok');
    }
}