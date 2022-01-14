<?php
declare(strict_types=1);
namespace App\Application\Users\Subscription\Get;

use App\Application\ApplicationService;
use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Subscription\SubscriptionRepository;
use App\Domain\Model\Authorization\SubscriptionsAuth;

class Get implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private SubscriptionsAuth $auth;
    private SubscriptionRepository $subscriptions;
    
    function __construct(SubscriptionRepository $subscriptions, UserRepository $users, SubscriptionsAuth $auth) {
        $this->subscriptions = $subscriptions;
        $this->users = $users;
        $this->auth = $auth;
    }
    
    public function execute(BaseRequest $request): GetResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $subscription = $this->subscriptions->getById($request->subId);
        if(!$subscription) {
            throw new \App\Application\Exceptions\NotExistException('Subscription not found');
        }
        //$this->auth->failIfCannotSee($requester, $subscription);
        $subscriptionsTransformer = new \App\DataTransformer\Users\SubscriptionTransformer();
        $dto = $subscriptionsTransformer->transform($subscription);
        return new GetResponse($dto);
    }

//    public function getValidationError() {
//        return $this->validationError;
//    }
}
