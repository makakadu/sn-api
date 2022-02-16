<?php
declare(strict_types=1);
namespace App\Application\Users\Connection\GetPart;

use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Post\PostRepository;
use App\DataTransformer\Users\PostTransformer;
use App\Application\GetRequestParamsValidator;
use App\Domain\Model\Users\Connection\ConnectionRepository;

class GetPart implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Users\Connection\ConnectionAppService;
    
    
    public function __construct(UserRepository $users, ConnectionRepository $connections) {
        $this->users = $users;
        $this->connections = $connections;
    }
    
    public function execute(BaseRequest $request): GetPartResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $user = $this->findUserOrFail($request->userId, true, null);
        
        $count = \is_null($request->count) ? 20 : (int)$request->count;
        $hideAccepted = \is_null($request->hideAccepted) ? false : (bool)$request->hideAccepted;
        $hidePending = \is_null($request->hidePending) ? false : (bool)$request->hidePending;
        $start = \is_null($request->start) ? null : (int)$request->start;
        
        $connections = $this->connections->getWithUser(
            $user, $request->cursor, ($count + 1), $hideAccepted, $hidePending, $request->type, $start
        );
        
        $cursor = null;
        if((count($connections) - $count) === 1) {
            $cursor = $connections[count($connections) -1]->id();
            array_pop($connections);
        }
        
        $allCount = $this->connections->getCountWithUser($user, $hideAccepted, $hidePending, $request->type, $start);
        
        $connsTransformer = new \App\DataTransformer\Users\ConnectionTransformer();
        $dtos = [];
        foreach ($connections as $connection) {
            $dtos[] = $connsTransformer->transform($connection);
        }
        return new GetPartResponse($dtos, $allCount, $cursor);
    }
    
//    function validate(GetRequest $request): void {
//        GetRequestParamsValidator::validateCountParam($request->count);
//        GetRequestParamsValidator::validateOffsetIdParam($request->offsetId);
//        GetRequestParamsValidator::validateCommentsTypeParam($request->commentsType);
//        GetRequestParamsValidator::validateCommentsCountParam($request->commentsCount);
//        GetRequestParamsValidator::validateCommentsOrderParam($request->commentsOrder, ['asc', 'desc', 'top']);
//    }
}
