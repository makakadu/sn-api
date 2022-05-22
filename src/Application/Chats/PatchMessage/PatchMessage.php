<?php
declare(strict_types=1);
namespace App\Application\Chats\PatchMessage;

use App\Application\BaseRequest;
use Pusher\Pusher;
use App\Domain\Model\Chats\MessageRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\DataTransformer\Chats\ActionTransformer;
use App\Domain\Model\Chats\ActionsEnum;

class PatchMessage implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Chats\ChatAppService;
    
    private Pusher $pusher;
    
    public function __construct(UserRepository $users, MessageRepository $messages, Pusher $pusher) {
        $this->users = $users;
        $this->pusher = $pusher;
        $this->messages = $messages;
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
            $lastAction = $chat->getLastAction();
            $actionDTO = (new ActionTransformer($requester))->transform($lastAction);
            $data = (array)$actionDTO;
            $this->pusher->trigger(
                $channels,
                ActionsEnum::DELETE_MESSAGE_FOR_ALL,
                $data
            );
        }
        else {
            throw new \App\Application\Exceptions\UnprocessableRequestException(123, "Incorrect param name");
        }
        return new PatchMessageResponse('OK');
    }

}
