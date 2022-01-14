<?php
declare(strict_types=1);
namespace App\Application\Users\Subscription\Delete;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Subscription\SubscriptionRepository;
use App\Application\Errors;

class Delete implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Users\Subscription\SubscriptionAppService;
    
    private SubscriptionRepository $subscriptions;
    
    function __construct(
        UserRepository $users, SubscriptionRepository $subscriptions
    ) {
        $this->users = $users;
        $this->subscriptions = $subscriptions;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $subscription = $this->findSubscriptionOrFail($request->subscriptionId, true);
        //$this->auth->failIfCannotDelete($requester, $subscription);
        if($requester->id() !== $subscription->subscriber()->id()) {
            throw new \App\Application\Exceptions\ForbiddenException(Errors::NO_RIGHTS, "No rights to delete subscription");
        }
        $this->subscriptions->remove($subscription);

        return new DeleteResponse('ok');
    }
}