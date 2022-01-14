<?php
declare(strict_types=1);
namespace App\Application\Groups\Membership\Accept;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Groups\Membership\MembershipRepository;

class Accept implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Groups\Membership\MembershipAppService;
    
    public function __construct(MembershipRepository $memberships, UserRepository $users) {
        $this->users = $users;
        $this->memberships = $memberships;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);

        $membership = $this->findMembershipOrFail($request->membershipId, true);
        $membership->accept($requester);
        
        return new AcceptResponse('ok');
    }
}