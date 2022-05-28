<?php
declare(strict_types=1);
namespace App\Application\Chats\CreateMessage;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Chats\MessageRepository;
use App\Domain\Model\Chats\ChatRepository;
use Pusher\Pusher;
use App\DataTransformer\Chats\MessageTransformer;
use App\Domain\Model\Chats\Action;
use App\DataTransformer\Chats\ChatTransformer;
use App\Domain\Model\Chats\ActionsEnum;
use App\DataTransformer\Chats\ActionTransformer;

class CreateMessage implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Chats\ChatAppService;
    
    private Pusher $pusher;
    private MessageTransformer $trans;
    private ChatTransformer $chatsTrans;
    
    public function __construct(UserRepository $users, ChatRepository $chats, MessageRepository $messages, Pusher $pusher, MessageTransformer $trans, ChatTransformer $chatsTrans) {
        $this->users = $users;
        $this->chats = $chats;
        $this->pusher = $pusher;
        $this->messages = $messages;
        $this->trans = $trans;
        $this->chatsTrans = $chatsTrans;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
//        $this->validateRequest($request);
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $chat = $this->findChatOrFail($request->chatId, true, null);
        
        $message = $chat->createMessage($requester, $request->text, $request->clientId);
        $this->messages->flush();
        
        $lastAction = $chat->getLastAction();
        foreach($chat->participants() as $participant) {
            $userId = $participant->userId();
            // Для каждого пользователя событие должно отличаться, потому что чат может выглядеть для участников по-разному
            $actionDTO = (new ActionTransformer($participant->user()))->transform($lastAction);
            $data = (array)$actionDTO;
            $data['placeId'] = $request->placeId;
            $this->pusher->trigger(
                'chat_' . $userId,
                ActionsEnum::CREATE_MESSAGE,
                $data
                // Возможно place_id нужно тоже хранить в Action, но я сделаю так только если это будет необходимо,
                // сейчас, вроде бы, оно нужно только для передачи события через вебсокет. А когда actions загружают через API, то там уже placeId
                // не нужен, вроде бы
            );
        }
        return new CreateMessageResponse($message->id());
    }
}