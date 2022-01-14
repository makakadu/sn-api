<?php
declare(strict_types=1);
namespace App\Application\Pages\UnbanUser;

use App\Application\BaseRequest;
use App\Application\BaseResponse;

class UnbanUser {
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $ban = $this->findBanOrFail($request->banId);
        $this->pagesAuth->failIfCannotDeleteBan($requester, $ban);

        $this->bans->remove($ban);
        
        return new UnbanUserResponse($ban);
    }

}
