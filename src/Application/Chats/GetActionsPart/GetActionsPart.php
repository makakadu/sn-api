<?php
declare(strict_types=1);
namespace App\Application\Chats\GetActionsPart;

use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Chats\ChatRepository;
use App\DataTransformer\Chats\ActionTransformer;

class GetActionsPart implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Chats\ChatAppService;
    
    public function __construct(UserRepository $users, ChatRepository $chats) {
        $this->users = $users;
        $this->chats = $chats;
    }
    
    public function execute(BaseRequest $request): GetActionsPartResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $chat = $this->findChatOrFail($request->chatId, true, null);
        $count = $request->count;
        $actions = $this->chats->getActionsForUser(
            $chat->id(),
            $requester,
            $request->types,
            $request->cursor ? (int)$request->cursor : null,
            $count ? $count + 1 : null
        );

        $cursor = null;
        if($count && (count($actions) - $count) === 1) {
            $cursor = $actions[count($actions) -1]->getCreatedAt()->getTimestamp();
            array_pop($actions);
        }
        return new GetActionsPartResponse(
            (new ActionTransformer($requester))->transformMultiple($actions),
            $cursor
       );
    }
}
