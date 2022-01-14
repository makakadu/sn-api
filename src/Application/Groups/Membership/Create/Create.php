<?php
declare(strict_types=1);
namespace App\Application\Groups\Membership\Create;

use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\Errors;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Groups\Group\GroupRepository;

class Create implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Groups\GroupAppServiceTrait;
    
    public function __construct(UserRepository $users, GroupRepository $groups) {
        $this->users = $users;
        $this->groups = $groups;
    }

    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $user = $this->findUserOrFail($request->userId, false, null);
        $group = $this->findGroupOrFail($request->groupId, false);
        
//        $existingMembership = $this->memberships->getByUserAndGroup($user, $group);
//        if($existingMembership && $existingMembership->isAccepted()) {
//            throw new UnprocessableRequestException(Errors::MEMBERSHIP_CREATED_AND_ACCEPTED);
//        }
//        elseif($existingMembership && !$existingMembership->isAccepted()) {
//            throw new UnprocessableRequestException(Errors::MEMBERSHIP_CREATED_AND_IS_PENDING);
//        }
//        $this->authorization->failIfCannotCreateMembership($requester, $user, $group);
//        
//        $membership = $requester->createMembership($user, $group);
//        $this->subscriptions->add($membership);
        
        $group->createMembership($requester, $user);
        
        return new CreateResponse('ok');
    }
}