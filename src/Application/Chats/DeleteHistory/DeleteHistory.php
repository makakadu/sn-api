<?php
declare(strict_types=1);
namespace App\Application\Chats\DeleteHistory;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\DataTransformer\Chats\MessageTransformer;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Chats\ChatRepository;
use App\Domain\Model\Chats\MessageRepository;
use Pusher\Pusher;
use App\DataTransformer\Chats\ChatTransformer;
use App\Domain\Model\Chats\ActionsEnum;
use App\DataTransformer\Chats\ActionTransformer;

class DeleteHistory implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Chats\ChatAppService;

    private Pusher $pusher;
    
    public function __construct(UserRepository $users, ChatRepository $chats, MessageRepository $messages, Pusher $pusher) {
        $this->users = $users;
        $this->chats = $chats;
        $this->messages = $messages;
        $this->pusher = $pusher;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
//        $this->validateRequest($request);
        
        $requester = $this->findRequesterOrFail($request->requesterId);
        $chat = $this->findChatOrFail($request->chatId, true);
        $currentParticipant = null;
        foreach($chat->participants() as $participant) {
            if($participant->user()->equals($requester)) {
                $currentParticipant = $participant;
                break;
            }
        }
        $currentParticipant->clearHistory();
        $this->chats->flush();
        
        $lastAction = $chat->getLastAction();
        $actionDTO = (new ActionTransformer($requester))->transform($lastAction);
        $data = (array)$actionDTO;
        $this->pusher->trigger(
            'chat_' . $currentParticipant->user()->id(),
            ActionsEnum::DELETE_HISTORY,
            $data
        );
        return new DeleteHistoryResponse("OK");
    }
}
