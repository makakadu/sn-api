<?php
declare(strict_types=1);
namespace App\Application\Users\GetUsers;

use App\Application\ApplicationService;
use App\Application\BaseRequest;

class GetUsers implements ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private \App\Domain\Model\Users\User\UserRepository $users;
    
    function __construct(\App\Domain\Model\Users\User\UserRepository $users) {
        $this->users = $users;
    }
    
    public function execute(BaseRequest $request) : GetUsersResponse  {
        $currentUserId = $request->requesterId;
        $users = [];
        if($request->ids) {
            $ids = explode(',', $request->ids);
            $users = $this->users->getByIds($ids);        
        } else if($request->page){

        } else if($request->username){
            $users = $this->users->getByUsername($request->username);
        }

        return new GetUsersResponse($currentUserId, $users, $this->users->getCount());
    }
}
