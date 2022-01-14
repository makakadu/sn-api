<?php
declare(strict_types=1);
namespace App\Application\Pages\Subscription\DeleteSubscription;

use App\Application\BaseRequest;
use App\Application\BaseResponse;

class DeleteSubscription extends \App\Application\Pages\Subscription\SubscriptionAppService {

    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $subscription = $this->findSubscriptionOrFail($request->subscriptionId);
        if($requester->equals($subscription->user())) {
            $this->subscriptions->remove($subscription);
        }
        return new DeleteSubscriptionResponse('ok');
    }
}