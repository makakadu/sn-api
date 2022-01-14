<?php
declare(strict_types=1);
namespace App\Application\Pages\Subscription\CreateSubscription;

use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\Errors;
use App\Application\BaseRequest;
use App\Application\BaseResponse;

class CreateSubscription {

    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $page = $this->findPageOrFail($request->pageId, false);

        $this->pagesAuth->failIfCannotSubscribe($requester, $page);
        
        $subscription = $requester->subscribeToPage($page);
        $this->subscriptions->add($subscription);
        exit();
        return new CreateSubscriptionResponse('ok');
    }
}