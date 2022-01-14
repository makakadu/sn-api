<?php
declare(strict_types=1);
namespace App\Application\Groups\Ban\UpdateBan;

use App\Application\BaseRequest;
use App\Application\BaseResponse;

class UpdateBan {
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $ban = $this->findBanOrFail($request->banId);
        $this->pagesAuth->failIfCannotEditBan($requester, $ban);
        
        if(!$request->minutes) {
            $subscription = $this->subscriptions->getByPageAndUser($ban->asPage(), $ban->user());
            if($subscription) {
                $this->subscriptions->remove($subscription);
            }
        }
        $ban->edit($request->minutes, $request->reason, $request->message);
        $this->bans->add($ban);
        
        return new EditBanResponse($ban);
    }

}
