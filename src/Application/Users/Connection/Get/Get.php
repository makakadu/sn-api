<?php
declare(strict_types=1);
namespace App\Application\Users\Connection\Get;

use App\Application\ApplicationService;
use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use App\Domain\Model\Authorization\ConnectionsAuth;

class Get implements ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Users\Connection\ConnectionAppService;
    
    private ConnectionsAuth $auth;
    
    function __construct(ConnectionRepository $connections, UserRepository $users, ConnectionsAuth $auth) {
        $this->connections = $connections;
        $this->users = $users;
        $this->auth = $auth;
    }
    
    public function execute(BaseRequest $request): GetResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $connection = $this->findConnectionOrFail($request->connectionId, true);
        
        $this->auth->failIfCannotSee($requester, $connection);
        
        $connsTransformer = new \App\DataTransformer\Users\ConnectionTransformer();
        $dto = $connsTransformer->transform($connection);
        return new GetResponse($dto);
    }

//    public function getValidationError() {
//        return $this->validationError;
//    }
}
