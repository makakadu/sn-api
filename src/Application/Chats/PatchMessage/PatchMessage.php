<?php
declare(strict_types=1);
namespace App\Application\Chats\PatchMessage;

use App\Application\BaseRequest;
use App\Application\Users\Posts\PostAppService;
use Pusher\Pusher;
use App\Domain\Model\Chats\ChatRepository;
use App\Domain\Model\Chats\MessageRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\DataTransformer\Chats\ChatTransformer;
use Doctrine\Common\Collections\Criteria;
use App\DataTransformer\Chats\MessageTransformer;

class PatchMessage implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Chats\ChatAppService;
    
    private Pusher $pusher;
    private ChatTransformer $trans;
    private MessageTransformer $messagesTrans;
    
    public function __construct(MessageTransformer $messagesTrans, UserRepository $users, ChatRepository $chats, MessageRepository $messages, Pusher $pusher, ChatTransformer $trans) {
        $this->users = $users;
        $this->chats = $chats;
        $this->pusher = $pusher;
        $this->messages = $messages;
        $this->messagesTrans = $messagesTrans;
        $this->trans = $trans;
    }

    public function execute(BaseRequest $request): PatchMessageResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $message = $this->findMessageOrFail($request->messageId, true);

        $channels = [];
        foreach($message->chat()->participants() as $participant) {
            $channels[] = 'chat_' . $participant->user()->id();
        }
        
        if($request->property === 'is_deleted') {
            $message->deleteForAll($requester);
            $this->messages->flush();
            
            $chat = $message->chat();
            $currentParticipant = $chat->getParticipantByUserId($requester->id());
            
            $criteria = Criteria::create();
            $criteria->where(Criteria::expr()->eq("deletedForAll", 0));
            $criteria->orderBy(array('id' => Criteria::DESC));
            $criteria->setMaxResults(1);

            $lastMessage = $currentParticipant->messages()->matching($criteria)->first();
            
            $this->pusher->trigger(
                $channels,
                'message-deleted-for-all',
                [
                    'chat' => $this->trans->transform($requester, $message->chat(), 0),
                    'chat_client_id' => $chat->clientId(),
                    'chat_unique_key' => $chat->getUniqueKey(),
                    'message_client_id' => $message->clientId(),
                    'message_id' => $message->id(),
                    'message_creator_id' => $message->creator()->id(),
                    'last_message' => $lastMessage ? $this->messagesTrans->transform($requester, $lastMessage) : null
                ]
            );
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
        


        return new PatchMessageResponse('OK');
    }

}
