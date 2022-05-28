<?php
declare(strict_types=1);
namespace App\Application\Chats\Patch;

use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Chats\ChatRepository;
use App\Domain\Model\Chats\MessageRepository;
use Pusher\Pusher;
use App\Domain\Model\Chats\ActionsEnum;
use App\DataTransformer\Chats\ActionTransformer;

class Patch implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Chats\ChatAppService;
    
    private Pusher $pusher;
    
    public function __construct(UserRepository $users, ChatRepository $chats, MessageRepository $messages, Pusher $pusher) {
        $this->users = $users;
        $this->chats = $chats;
        $this->messages = $messages;
        $this->pusher = $pusher;
    }

    public function execute(BaseRequest $request): PatchResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $chat = $this->findChatOrFail($request->chatId, true);

        $channels = [];
        foreach($chat->participants() as $participant) {
            $channels[] = 'chat_' . $participant->userId();
        }
        
        if($request->property === 'last_read_message') {
            $message = $this->findMessageOrFail($request->value, false);
            $chat->read($requester, $message);
            $this->chats->flush();

            $actionDTO = (new ActionTransformer($requester))->transform($chat->getLastAction());
            $data = (array)$actionDTO;
            $this->pusher->trigger($channels, ActionsEnum::READ_MESSAGE, $data);
        }
//        elseif($request->property === 'reactions_are_disabled') {
//            $request->value
//                ? $post->disableReactions($requester)
//                : $post->enableReactions($requester);
//        }
//        elseif($request->property === 'is_public') {
//            $post->changeIsPublic($requester, $request->value);
//        }
//        elseif($request->property === 'deleted') {
//            $request->value
//                ? $post->delete($requester)
//                : $post->restore($requester);
//        }
//        elseif($request->property === 'deleted_by_global_moderation') {
//            $request->value
//                ? $post->deleteByGlobalModer($requester)
//                : $post->restoreByGlobalModer($requester);
//        }
        else {
            throw new \App\Application\Exceptions\UnprocessableRequestException(123, "Incorrect param name");
        }
        return new PatchResponse('OK');
    }

}
