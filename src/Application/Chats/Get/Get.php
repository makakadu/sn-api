<?php
declare(strict_types=1);
namespace App\Application\Chats\Get;

use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Application\GetRequestParamsValidator;
use App\Domain\Model\Chats\ChatRepository;
use App\DataTransformer\Chats\ChatTransformer;
use App\Domain\Model\Chats\MessageRepository;

class Get implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Chats\ChatAppService;
    
    private ChatTransformer $chatTransformer;
    
    public function __construct(UserRepository $users, ChatRepository $chats, MessageRepository $messages, ChatTransformer $chatTransformer) {
        $this->users = $users;
        $this->chats = $chats;
        $this->messages = $messages;
        $this->chatTransformer = $chatTransformer;
    }
    
    public function execute(BaseRequest $request): GetResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
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
        
        $messagesCount = $request->messagesCount
            ? (int)$request->messagesCount + 1
            : 10;
        
        return new GetResponse(
            $this->chatTransformer->transform($requester, $chat, $messagesCount)
        );
    }
}
