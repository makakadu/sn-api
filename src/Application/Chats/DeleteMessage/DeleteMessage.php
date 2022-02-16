<?php
declare(strict_types=1);
namespace App\Application\Chats\DeleteMessage;

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

class DeleteMessage implements \App\Application\ApplicationService {
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
        $message = $currentParticipant->messages()->get($request->messageId);
        if(!$message) {
            throw new \App\Application\Exceptions\NotExistException('Message not found');
        }
        $currentParticipant->messages()->remove($request->messageId);
        $this->chats->flush();
        
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq("deletedForAll", false));
        $criteria->orderBy(array('id' => Criteria::DESC));
        $criteria->setMaxResults(1);
        
        $lastMessage = $currentParticipant->messages()->matching($criteria)->first();
//        echo $lastMessage->id();exit();
        
        $this->pusher->trigger(
            'chat_' . $currentParticipant->user()->id(),
            'delete-message',
            [
                'message_id' => $request->messageId,
                'message_creator_id' => $message->creator()->id(),
                'chat' => $this->chatTransformer->transform($requester, $chat, 0),
                'last_message' => !\is_null($lastMessage) ? $this->transformer->transform($requester, $lastMessage) : null
            ]
        );
        
        return new DeleteMessageResponse("OK");
    }
}
