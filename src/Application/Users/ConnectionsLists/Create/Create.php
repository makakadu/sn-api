<?php
declare(strict_types=1);
namespace App\Application\Users\ConnectionsLists\Create;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\Users\ConnectionAppService;
use App\Domain\Model\Users\Connection\ConnectionRepository;

class Create extends ConnectionAppService {
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);

        $list = $requester->createConnectionsList($request->name);
        
        $connections = $this->connections->getByIds($request->connections);
        foreach($connections as $connection) {
            if($requester->id() === $connection->initiatorId() || $requester->id() === $connection->targetId()) {
                $list->addConnection($connection);
            } else {
                //throw new \App\Application\Exceptions\UnprocessableRequestException(Errors::);
            }
        }
        return new CreateResponse('ok');
    }
}