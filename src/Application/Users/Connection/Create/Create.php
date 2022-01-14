<?php
declare(strict_types=1);
namespace App\Application\Users\Connection\Create;

use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\Errors;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\Users\ConnectionAppService;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use App\Domain\Model\Authorization\ConnectionsAuth;
use App\Domain\Model\Users\Subscription\SubscriptionRepository;
use Assert\Assertion;
use App\Application\Exceptions\ValidationException;
use App\Domain\Model\DomainExceptionAlt;

class Create extends ConnectionAppService {
    
    private SubscriptionRepository $subscriptions;
    
    function __construct(
        UserRepository $users, ConnectionRepository $connections,
        ConnectionsAuth $auth, SubscriptionRepository $subscriptions
    ) {
        parent::__construct($users, $connections, $auth);
        $this->subscriptions = $subscriptions;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $requestee = $this->findUserOrFail($request->requesteeId, false, "Target user '{$request->requesteeId}' not found");
        $this->validateRequest($request);
        
        $existingConnection = $this->connections->getByUsersIds($requester->id(), $requestee->id());

        if($existingConnection && $existingConnection->isAccepted()) {
            throw new DomainExceptionAlt(['code' => Errors::ALREADY_FRIENDS, 'message' => 'Already friends', 'connection_id' => $existingConnection->id()]);
        }
        elseif($existingConnection && $existingConnection->initiatorId() === $requester->id()) {
            throw new DomainExceptionAlt(['code' => Errors::OFFER_ALREADY_SENT, 'message' => 'Request already sent', 'connection_id' => $existingConnection->id()]);
        }
        elseif($existingConnection && $existingConnection->targetId() === $requester->id()) {
            throw new DomainExceptionAlt(['code' => Errors::MUTUAL_OFFER_RECEIVED, 'message' => 'Request received', 'connection_id' => $existingConnection->id()]);
        }
        $this->auth->failIfCannotOfferConnection($requester, $requestee); 

        $connection = $requester->createConnection($requestee);
        $this->connections->add($connection);

        $createdSubscriptionId = null;
        $existingSubscription = $this->subscriptions->getByUsersIds($requester->id(), $requestee->id());
        if(!$existingSubscription && $request->subscribe) {
            $subscription = $requester->subscribe($requestee);
            $this->subscriptions->add($subscription);
            $createdSubscriptionId = $subscription->id();
        }

        return new CreateResponse($connection->id(), $createdSubscriptionId);
    }
    
    function validateRequest(CreateRequest $request): void {
        try {
            Assertion::string($request->requesteeId, "Param 'target_user_id' should be a string");
            //Assertion::boolean($request->subscribe, "Param 'subscribe' should be a boolean");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new ValidationException($ex->getMessage());
        }

    }
}