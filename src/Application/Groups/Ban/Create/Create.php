<?php
declare(strict_types=1);
namespace App\Application\Groups\Ban\Create;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Groups\Ban\BanRepository;
use App\Domain\Model\Groups\Group\GroupRepository;

class Create implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Groups\GroupAppServiceTrait;
    
    //private PagesAuth $pagesAuth;
    private BanRepository $bans;
    
    public function __construct(UserRepository $users, GroupRepository $groups, BanRepository $bans) {//, PagesAuth $pagesAuth) {
        $this->users = $users;
        $this->bans = $bans;
        $this->groups = $groups;
        //$this->pagesAuth = $pagesAuth;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $group = $this->findGroupOrFail($request->groupId, false, null);
        $target = $this->findUserOrFail($request->userId, false, null);

        $existingBan = $this->bans->getByGroupAndUser($group, $target);
        if($existingBan) {
            $banEnd = $existingBan->getEnd();
            if($banEnd && $banEnd < (new \DateTime('now')) && $existingBan->deletedAt() === "") {
                $this->bans->remove($existingBan);
                $this->bans->flush();
            }
        }
        
        $ban = $group->banUser($requester, $target, $request->minutes, $request->reason, $request->message);

        return new CreateResponse($ban);
    }

}
