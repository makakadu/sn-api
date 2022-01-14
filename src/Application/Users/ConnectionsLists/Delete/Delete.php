<?php
declare(strict_types=1);
namespace App\Application\Users\ConnectionsLists\Delete;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\Exceptions\ForbiddenException;
use App\Application\Users\ConnectionsLists\ConnectionsListAppService;

class Delete extends ConnectionsListAppService {
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $list = $this->findListOrFail($request->listId);
        if(!$requester->equals($list->user())) {
            $message = "Cannot delete connections list '{$list->id()}' because it belongs to another user";
            throw new ForbiddenException(123, $message);
        }
        $this->lists->remove($list);
        
        return new DeleteResponse("OK");
    }

}