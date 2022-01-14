<?php
declare(strict_types=1);
namespace App\Application\Pages\Subscription\UpdateSubscription;

use App\Application\BaseRequest;
use App\Application\BaseResponse;

class UpdateSubscription {
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);

        $subscription = $this->findSubscriptionOrFail($request->subscriptionId);
        $this->subscriptionsAuth->failIfCannotUpdateComment($requester, $subscription);
        
        foreach ($request->payload as $key => $value) {
            if($key === 'pause') {
                $subscription->pause($requester, $value);
            }
            elseif($key === 'is_disabled') {

                if($value) {
                    $subscription->disable($requester);
                } else {
                    $subscription->enable($requester);
                }
            }
        }
        
        exit();
        return new CreateConnectionResponse('ok');
    }
}