<?php
declare(strict_types=1);
namespace App\Application\Users\GetUser;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\DataTransformer\Users\ProfileTransformer;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use App\DataTransformer\Users\ConnectionTransformer;
use App\Domain\Model\Users\Subscription\SubscriptionRepository;

class GetUser implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private ProfileTransformer $transformer;
    private ConnectionRepository $connections;
    private ConnectionTransformer $connsTrans;
    
    public function __construct(
        ProfileTransformer $transformer,
        UserRepository $users, 
        ConnectionRepository $connections,
        ConnectionTransformer $connsTrans
    ) {
        $this->users = $users;
        $this->transformer = $transformer;
        $this->connections = $connections;
        $this->connsTrans = $connsTrans;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        $user = $this->findUserOrFail($request->requestedUserId, true, null);
        
        $connections = $this->connections->getWithUser($user, null, 6, false, true, null, null);
        $acceptedConnsDTOs = $this->connsTrans->transformMultiple($connections);
        $allAcceptedConnsCount = $this->connections->getCountWithUser($user, false, true, null, null);

        $subscriptions = $this->users->getUserSubscriptions($user, null, 6);
        $subscriptionsDTOs = $this->transformer->transformMultipleToSubscriberDTO($subscriptions);
        $subscriptionsCount = $this->users->getSubscriptionsCount($user);
        $subscribers = $this->users->getUserSubscribers($user, null, 6);
        $subscribersDTOs = $this->transformer->transformMultipleToSubscriberDTO($subscribers);
        $subscribersCount = $this->users->getSubscribersCount($user);
        
        $dto = null;
        if($user->isBlocked()) {
            $dto = \App\DTO\BlockedProfileDTO(
                $user->id(),
                $user->firstName(),
                $user->lastName(),
                (string)$user->username()
            );
        } else {
            $dto = $this->transformer->transform($requester, $user);
            $dto->acceptedConnections = $acceptedConnsDTOs;
            $dto->allAcceptedConnections = $allAcceptedConnsCount;
            $dto->subscriptions = $subscriptionsDTOs;
            $dto->subscriptionsCount = $subscriptionsCount;
            $dto->subscribers = $subscribersDTOs;
            $dto->subscribersCount = $subscribersCount;
        }
        return new GetUserResponse($dto);
    }
}
