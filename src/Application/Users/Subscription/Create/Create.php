<?php
declare(strict_types=1);
namespace App\Application\Users\Subscription\Create;

use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\Errors;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\Subscription\SubscriptionRepository;
use App\Domain\Model\Users\User\UserRepository;

class Create implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private SubscriptionRepository $subscriptions;

    function __construct(SubscriptionRepository $subscriptions, UserRepository $users) {
        $this->subscriptions = $subscriptions;
        $this->users = $users;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $requestee = $this->findUserOrFail($request->requesteeId, false, null);
        
        $existingSubscription = $this->subscriptions->getByUsersIds($requester->id(), $requestee->id());
        if($existingSubscription) {
            throw new \App\Domain\Model\DomainExceptionAlt(['code' => Errors::ALREADY_SUBSCRIBED, 'message' => 'Already subscribed', 'subscription_id' => $existingSubscription->id()]);
        }
        //$this->authorization->failIfCannotSubscribe($requester, $requestee);
        
        $subscription = $requester->subscribe($requestee);
        $this->subscriptions->add($subscription);
        //exit();
        return new CreateResponse($subscription->id());
    }
}