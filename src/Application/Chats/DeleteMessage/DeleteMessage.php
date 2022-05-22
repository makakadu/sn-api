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
use App\Domain\Model\Chats\ActionsEnum;
use App\DataTransformer\Chats\ActionTransformer;

class DeleteMessage implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Chats\ChatAppService;
    
    private MessageTransformer $transformer;
    private ChatTransformer $chatTransformer;
    private Pusher $pusher;
    
    public function __construct(UserRepository $users, ChatRepository $chats, Pusher $pusher) {
        $this->users = $users;
        $this->chats = $chats;
        $this->pusher = $pusher;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
//        $this->validateRequest($request);
        $requester = $this->findRequesterOrFail($request->requesterId);
        $chat = $this->findChatOrFail($request->chatId, true);
        $chat->removeMessageFor($request->messageId, $requester);
        $this->chats->flush();
        
        $lastAction = $chat->getLastAction();
        $actionDTO = (new ActionTransformer($requester))->transform($lastAction);
        
        /* Я пока точно не знаю стоит ли рассылать это событие всем участникам, потому что всё-таки это приватное действие
         но прикол в том, что если пользователь удалил сообщение, значит он его прочитал. Если позволить пользователю удалять сообщение через
         прямой запрос на API, то нужно помечать это сообщение как прочитанное им. 
         Но сейчас я не вижу смысла усложнять, к тому же это событие возможно получить только зная приватный ключ, поэтому я считаю, что 
         не стоит таким заниматься сейчас. Это событие будет доставлено только пользователю, который совершил удаление */
        $data = (array)$actionDTO;
        $this->pusher->trigger(
            'chat_' . $chat->getParticipantByUserId($requester->id()),
            ActionsEnum::DELETE_MESSAGE,
            $data
        );
        
        return new DeleteMessageResponse("OK");
    }
}
