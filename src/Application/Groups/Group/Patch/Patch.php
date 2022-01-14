<?php
declare(strict_types=1);
namespace App\Application\Groups\Group\Patch;

use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Groups\Group\GroupRepository;

class Patch implements \App\Application\ApplicationService { 
    use \App\Application\AppServiceTrait;
    use \App\Application\Pages\Posts\PostAppServiceTrait;
    use \App\Application\Groups\GroupAppServiceTrait;
    
    public function __construct(UserRepository $users, GroupRepository $groups) {
        $this->users = $users;
        $this->groups = $groups;
    }
    
    public function execute(BaseRequest $request): PatchResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $group = $this->findGroupOrFail($request->groupId, true);

        foreach ($request->payload as $key => $value) {
            if($key === 'name') {
                $group->changeName($requester, $value);
            }
            else {
                throw new \App\Application\Exceptions\UnprocessableRequestException(123, "Incorrect param name");
            }
        }

        return new PatchResponse('OK');
    }

}
