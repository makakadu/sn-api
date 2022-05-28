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
use App\Domain\Model\Chats\Chat;
use App\Domain\Model\Chats\ActionsEnum;
use App\DataTransformer\Chats\ActionTransformer;

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
        if(!in_array($request->type, [Chat::PAIR_CHAT, Chat::GROUP_CHAT])) {
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
        $chat = $requester->createChat($participants, $request->clientId, $request->type, $request->firstMessage, $request->messageClientId);
        $this->chats->add($chat);
        $this->chats->flush();
        $lastChatAction = $chat->getLastAction();
        
        foreach($chat->participants() as $participant) {
            $user = $participant->user();
            $actionDTO = (new ActionTransformer($user))->transform($lastChatAction);

            $data = (array)$actionDTO;
            $data['place_id'] = $request->placeId;
            $this->pusher->trigger(
                'chat_' . $participant->userId(),
                ActionsEnum::CREATE_CHAT,
                $data
            );
        }
        $firstMessage = $chat->messages()->first();
        
        return new CreateResponse($chat->id(), $firstMessage ? $firstMessage->id() : null);
    }
}