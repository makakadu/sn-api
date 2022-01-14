<?php
declare(strict_types=1);
namespace App\Application\Users\Connection\Accept;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\Users\ConnectionAppService;

class Accept extends ConnectionAppService{
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $connection = $this->findConnectionOrFail($request->connectionId);

        $connection->accept($requester);
        return new AcceptResponse('ok');
    }
}