<?php
declare(strict_types=1);
namespace App\Application\Chats\DeleteHistory;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\DataTransformer\Chats\MessageTransformer;
use App\Domain\Model\Pages\Post\PostRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Application\Pages\Posts\PostParamsValidator;
use App\Domain\Model\Chats\ChatRepository;
use App\Domain\Model\Chats\MessageRepository;
use Pusher\Pusher;
use App\DataTransformer\Chats\ChatTransformer;
use Doctrine\Common\Collections\Criteria;

class DeleteHistory implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Chats\ChatAppService;
    
    private MessageTransformer $transformer;
    private ChatTransformer $chatTransformer;
    private Pusher $pusher;
    
    public function __construct(UserRepository $users, ChatRepository $chats, MessageRepository $messages, MessageTransformer $transformer, Pusher $pusher, ChatTransformer $chatTransformer) {
        $this->users = $users;
        $this->chats = $chats;
        $this->messages = $messages;
        $this->transformer = $transformer;
        $this->chatTransformer = $chatTransformer;
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
        
        $this->pusher->trigger(
            'chat_' . $currentParticipant->user()->id(),
            'delete-history',
            [
                'user_id' => $requester->id(),
                'chat_id' => $chat->id(),
            ]
        );
        
        return new DeleteHistoryResponse("OK");
    }
}
