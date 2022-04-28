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

class CreateMessage implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Chats\ChatAppService;
    
    private Pusher $pusher;
    private MessageTransformer $trans;
    
    public function __construct(UserRepository $users, ChatRepository $chats, MessageRepository $messages, Pusher $pusher, MessageTransformer $trans) {
        $this->users = $users;
        $this->chats = $chats;
        $this->pusher = $pusher;
        $this->messages = $messages;
        $this->trans = $trans;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
//        $this->validateRequest($request);
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $chat = $this->findChatOrFail($request->chatId, true, null);
        
        $message = $chat->createMessage($requester, $request->text, $request->clientId);
        $this->messages->flush();
        
        $channels = [];
        foreach($chat->participants() as $participant) {
            $channels[] = 'chat_' . $participant->user()->id();
        }

        $this->pusher->trigger(
            $channels,
            'create-message',
            [
                'chat_id' => $chat->id(),
                'chat_client_id' => $chat->clientId(),
                'chat_unique_key' => $chat->getUniqueKey(),
                'message_client_id' => $message->clientId(),
                'message' => $this->trans->transform($requester, $message)
            ]
        );
        
        return new CreateMessageResponse($message->id());
    }
}