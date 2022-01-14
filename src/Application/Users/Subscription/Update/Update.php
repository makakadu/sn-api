<?php
declare(strict_types=1);
namespace App\Application\Users\Subscription\Update;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use Assert\Assert;
use App\Domain\Model\Users\Subscription\SubscriptionRepository;
use App\Domain\Model\Users\User\UserRepository;

class Update implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Users\Subscription\SubscriptionAppService;

    function __construct(
        UserRepository $users, SubscriptionRepository $subscriptions
    ) {
        $this->users = $users;
        $this->subscriptions = $subscriptions;
    }

    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);

        $subscription = $this->findSubscriptionOrFail($request->subscriptionId, true);
        if($requester->id() !== $subscription->subscriberId()) {
            throw new \App\Application\Exceptions\ForbiddenException(Errors::NO_RIGHTS, "No rights to modify subscription");
        }
        
        if($request->property === 'pause_duration_in_days') {
            $value = $request->value;
            Assert::that($value)
                ->integer("'pause_duration_in_days' should be an integer")
                ->between(1, 3650, "Value of 'pause_duration_in_days' should be between 1 and 3650");
            $value ? $subscription->pause($value) : $subscription->unpause();
        }
        //exit();
        return new UpdateResponse('ok');
    }
}