<?php
declare(strict_types=1);
namespace App\Application\Users\Ban\Create;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Connection\ConnectionRepository;

class Create implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private ConnectionRepository $connections;
    
    function __construct(UserRepository $users, ConnectionRepository $connections) {
        $this->users = $users;
        $this->connections = $connections;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $user = $this->findUserOrFail($request->userId, false, null);
        
        $requester->ban($user);

        $connection = $this->connections->getByUsersIds($requester->id(), $user->id());
        if($connection) {
            $this->connections->remove($connection);
        }
        return new CreateResponse("OK");
    }

}