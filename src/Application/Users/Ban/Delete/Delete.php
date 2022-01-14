<?php
declare(strict_types=1);
namespace App\Application\Users\Ban\Delete;

use App\Application\BaseRequest;
use App\Application\BaseResponse;

class Delete {
    
    // BanRepository
    // UserRepository
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);

        $ban = $this->findBanOrFail($request->banId);
        if(!$requester->equals($ban->creator())) {
            throw new UnprocessableRequestException(
                123, "Cannot delete ban '{$ban->id()}', access is forbidden"
            );
        }
        $this->bans->remove($ban);
        
        return new DeleteResponse("OK");
    }

}