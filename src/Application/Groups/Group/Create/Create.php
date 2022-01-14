<?php
declare(strict_types=1);
namespace App\Application\Groups\Group\Create;

use App\Application\Users\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Application\RequestParamsValidator;
use App\Domain\Model\Common\Shares\SharesService;
use App\Domain\Model\Common\AttachmentsService;
use App\Domain\Model\Groups\Group\GroupRepository;

class Create implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private GroupRepository $groups;
            
    function __construct(
        UserRepository $users,
        GroupRepository $groups
    ) {
        $this->users = $users;
        $this->groups = $groups;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);

        $group = $requester->createGroup($request->name, (bool)$request->private, (bool)$request->visible);
        $this->groups->add($group);
        
        return new CreateResponse($group->id());
    }
}