<?php
declare(strict_types=1);
namespace App\Application\Users\Subscription\GetSubscribers;

use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\DataTransformer\Users\ProfileTransformer;

class GetSubscribers implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Users\Subscription\SubscriptionAppService;
    
    
    public function __construct(UserRepository $users, ProfileTransformer $transformer) {
        $this->users = $users;
        $this->transformer = $transformer;
    }
    
    public function execute(BaseRequest $request): GetSubscribersResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $user = $this->findUserOrFail($request->userId, true, null);
        $count = \is_null($request->count) ? 20 : (int)$request->count;
        
        $subscribers = $this->users->getUserSubscribers($user, $request->cursor, ($count + 1));
        
        $cursor = null;
        if((count($subscribers) - $count) === 1) {
            $cursor = $subscribers[count($subscribers) -1]->id();
            array_pop($subscribers);
        }
        $subscribersCount = $this->users->getSubscribersCount($user);

        $dtos = [];
        foreach ($subscribers as $subscriber) {
            $dtos[] = $this->transformer->transform($requester, $subscriber);
        }
        return new GetSubscribersResponse($dtos, (int)$subscribersCount, $cursor);
    }
    
//    function validate(GetRequest $request): void {
//        GetRequestParamsValidator::validateCountParam($request->count);
//        GetRequestParamsValidator::validateOffsetIdParam($request->offsetId);
//        GetRequestParamsValidator::validateCommentsTypeParam($request->commentsType);
//        GetRequestParamsValidator::validateCommentsCountParam($request->commentsCount);
//        GetRequestParamsValidator::validateCommentsOrderParam($request->commentsOrder, ['asc', 'desc', 'top']);
//    }
}
