<?php
declare(strict_types=1);
namespace App\Application\Chats\TypingMessage;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Chats\ChatRepository;
use Pusher\Pusher;
use App\DataTransformer\Chats\ChatTransformer;
use App\DataTransformer\Chats\MessageTransformer;

class TypingMessage implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Chats\ChatAppService;
    
    private Pusher $pusher;
    
    public function __construct(UserRepository $users, ChatRepository $chats, Pusher $pusher) {
        $this->users = $users;
        $this->chats = $chats;
        $this->pusher = $pusher;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $chat = $this->findChatOrFail($request->chatId, true, null);
        
        $isParticipant = false;
        foreach($chat->participants() as $participant) {
            if($participant->user()->equals($requester)) {
                $isParticipant = true;
                break;
            }
        }
        if(!$isParticipant) {
            throw new \App\Application\Exceptions\ForbiddenException(228, "No rights"); 
        }
        
        $channels = [];
        foreach($chat->participants() as $participant) {
            if($participant->user()->id() !== $requester->id()) {
                $channels[] = 'chat_' . $participant->userId();
            }
        }
        
        $this->pusher->trigger(
            $channels,
            'typing-message',
            [
                'creator_id' => $requester->id(),
                'chat_id' => $chat->id()
            ]
        );
        
        return new TypingMessageResponse('ok');
    }
}