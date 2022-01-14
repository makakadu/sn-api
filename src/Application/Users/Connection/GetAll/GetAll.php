<?php
declare(strict_types=1);
namespace App\Application\Users\Connection\GetAll;

use App\Application\ApplicationService;
use App\Application\BaseRequest;

use App\Domain\Model\Users\Connection\ConnectionRepository;

class GetAll implements ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private ConnectionRepository $connections;
    
    function __construct(ConnectionRepository $connections) {
        $this->connections = $connections;
    }
    
    public function execute(BaseRequest $request): GetAllResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $user = $this->findUserOrFail($request->userId, false, null);
        
        $connections = $this->connections->getByOwnerId($request->ownerId);
        
        return new GetAllResponse($connections);
    }
}
