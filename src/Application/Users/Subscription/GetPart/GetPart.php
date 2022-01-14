<?php
declare(strict_types=1);
namespace App\Application\Users\Subscription\GetPart;

use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Subscription\SubscriptionRepository;
use App\DataTransformer\Users\ProfileTransformer;

class GetPart implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Users\Subscription\SubscriptionAppService;
    
    
    public function __construct(UserRepository $users, ProfileTransformer $transformer, SubscriptionRepository $subscriptions) {
        $this->users = $users;
        $this->subscriptions = $subscriptions;
        $this->transformer = $transformer;
    }
    
    public function execute(BaseRequest $request): GetPartResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $user = $this->findUserOrFail($request->userId, true, null);
        $count = \is_null($request->count) ? 20 : (int)$request->count;
        
        $subscriptions = $this->users->getUserSubscriptions(
            $user, $request->cursor, ($count + 1)
        );
        
        $cursor = null;
        if((count($subscriptions) - $count) === 1) {
            $cursor = $subscriptions[count($subscriptions) -1]->id();
            array_pop($subscriptions);
        }
        $allCount = $this->users->getSubscriptionsCount($user);

        $dtos = [];
        foreach ($subscriptions as $subscription) {
            $dtos[] = $this->transformer->transform($requester, $subscription);
        }

        return new GetPartResponse($dtos, (int)$allCount, $cursor);
    }
    
//    function validate(GetRequest $request): void {
//        GetRequestParamsValidator::validateCountParam($request->count);
//        GetRequestParamsValidator::validateOffsetIdParam($request->offsetId);
//        GetRequestParamsValidator::validateCommentsTypeParam($request->commentsType);
//        GetRequestParamsValidator::validateCommentsCountParam($request->commentsCount);
//        GetRequestParamsValidator::validateCommentsOrderParam($request->commentsOrder, ['asc', 'desc', 'top']);
//    }
}
