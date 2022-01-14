<?php
declare(strict_types=1);
namespace App\Application\Groups\CreateGroupBan;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Groups\Membership\Membership;
use App\Domain\Model\Groups\Group\GroupRepository;

class CreateGroupBan {
    
    private GroupRepository $groups;
    
    public function execute(BaseRequest $request): BaseResponse {
//        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
//        $group = $this->findUserOrFail($request->groupId, false);
//        $user = $this->findUserOrFail($request->userId, false);
//        
//        $this->authorization->failIfCannotBan($requester, $user, $group);
//        $ban = $group->ban($user, $request->time);
//        
//        if($ban->isPermanent()) {
//            $membership = $this->memberships->getByUserAndGroup($user, $group);
//            if($membership) {
//                $this->memberships->remove($membership);
//            }
//            else {
//                $membershipRequest = $this->membershipsRequests->getByUserAndGroup($user, $group);
//                if($membershipRequest) {
//                    $this->membershipsRequests->remove($membershipRequest);
//                }
//                else {
//                    $membershipInvite = $this->membershipsInvites->getByUserAndGroup($user, $group);
//                    if($membershipInvite) {
//                        $this->membershipsInvites->remove($membershipInvite);
//                    }
//                }
//            }
//        }
//        
//        $this->groupsBans->add($ban);
//        
        return new CreateGroupBanResponse();
    }

}
