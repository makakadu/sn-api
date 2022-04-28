<?php
declare(strict_types=1);
namespace App\Application\Chats\Patch;

use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Chats\ChatRepository;
use App\Domain\Model\Chats\MessageRepository;
use Pusher\Pusher;
use Doctrine\Common\Collections\Criteria;

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
            $channels[] = 'chat_' . $participant->user()->id();
        }
        
        if($request->property === 'last_read_message') {
            $message = $this->findMessageOrFail($request->value, false);
            $chat->read($requester, $message);
            $this->chats->flush();
            
            $currentParticipant = $chat->getParticipantByUserId($requester->id());

            $criteria2 = Criteria::create();
            $criteria2->where(Criteria::expr()->eq("deletedForAll", 0));
            $criteria2->andWhere(Criteria::expr()->neq("creatorId", $requester->id()));
            $criteria2->andWhere(Criteria::expr()->gt("id", $request->value));
            $unreadMessagesCount = $currentParticipant->messages()->matching($criteria2)->count();
            
            $this->pusher->trigger(
                $channels,
                'update-last-read-message-id',
                [
                    'chat_id' => $chat->id(),
                    'chat_unique_key' => $chat->getUniqueKey(),
                    'unread_messages_count' => $unreadMessagesCount,
                    'last_mead_message_id' => $request->value,
                    'user_id' => $requester->id()
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
        return new PatchResponse('OK');
    }

}
