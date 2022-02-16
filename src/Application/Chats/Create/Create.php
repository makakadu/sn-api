<?php
declare(strict_types=1);
namespace App\Application\Chats\Create;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Chats\ChatRepository;
use Pusher\Pusher;
use App\DataTransformer\Chats\ChatTransformer;
use App\DataTransformer\Chats\MessageTransformer;

class Create implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Chats\ChatAppService;
    
    private Pusher $pusher;
    private ChatTransformer $trans;
    private MessageTransformer $messagesTrans;
    
    public function __construct(UserRepository $users, ChatRepository $chats, Pusher $pusher, ChatTransformer $trans, MessageTransformer $messagesTrans) {
        $this->users = $users;
        $this->chats = $chats;
        $this->pusher = $pusher;
        $this->trans = $trans;
        $this->messagesTrans = $messagesTrans;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
//        $this->validateRequest($request);
        if(!in_array($request->type, ['pair_user_chat', 'group_user_chat'])) {
            throw new \App\Application\Exceptions\ValidationException('Invalid chat type');
        }
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $participants = [];
        if($request->participants) {
            foreach($request->participants as $participantId) {
                $user = $this->findUser($participantId);
                if(!$user) {
                    throw new \App\Application\Exceptions\UnprocessableRequestException(228, "Participant '$participantId' not found");
                }
                $participants[] = $user;
            }
        }
        $chat = $requester->createChat($participants, $request->type, $request->firstMessage, $request->frontKey);
        $this->chats->add($chat);
        $this->chats->flush();
        
        $channels = [];
        foreach($chat->participants() as $participant) {
            $channels[] = 'chat_' . $participant->user()->id();
        }

        $this->pusher->trigger(
            $channels,
            'create-chat',
            ['chat' => $this->trans->transform($requester, $chat)]
        );
        
        $firstMessage = null;
        if($request->firstMessage) {
            $firstMessage = $chat->messages()->first();
            if($firstMessage) {
                $this->pusher->trigger(
                    $channels,
                    'create-message',
                    [
                        'chat_id' => $chat->id(),
                        'message' => $this->messagesTrans->transform($requester, $firstMessage),
                        'front_key' => $request->frontKey
                    ]
                );
            }
        }
        
        return new CreateResponse($chat->id(), $firstMessage ? $firstMessage->id() : null);
    }
}