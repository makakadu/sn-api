<?php
declare(strict_types=1);
namespace App\Application\Users\Connection\Delete;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\Users\ConnectionAppService;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use App\Domain\Model\Authorization\ConnectionsAuth;
use App\Domain\Model\Users\Subscription\SubscriptionRepository;
use Pusher\Pusher;

class Delete implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Users\Connection\ConnectionAppService;
    
    private SubscriptionRepository $subscriptions;
    private ConnectionsAuth $auth;
    
    function __construct(
        UserRepository $users, ConnectionRepository $connections,
        ConnectionsAuth $auth, SubscriptionRepository $subscriptions,
        Pusher $pusher
    ) {
        $this->subscriptions = $subscriptions;
        $this->users = $users;
        $this->auth = $auth;
        $this->pusher = $pusher;
        $this->connections = $connections;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $connection = $this->findConnectionOrFail($request->connectionId, true);
        $this->auth->failIfCannotDelete($requester, $connection);
        //$connection->delete($requester);
        $this->connections->remove($connection);
        $this->connections->flush();

        $this->pusher->trigger(
            [
                $connection->getUser1()->id(),
                $connection->getUser2()->id(),
            ], 
            'delete-connection',
            [
                'id' => $connection->id(),
            ]
        );

        return new DeleteResponse('ok');
    }
}