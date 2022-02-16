<?php
declare(strict_types=1);
namespace App\Application\Chats\GetMessage;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\DataTransformer\Chats\MessageTransformer;
use App\Domain\Model\Pages\Post\PostRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Application\Pages\Posts\PostParamsValidator;
use App\Domain\Model\Chats\ChatRepository;
use App\Domain\Model\Chats\MessageRepository;

class GetMessage implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Chats\ChatAppService;
    
    private MessageTransformer $transformer;
    
    public function __construct(UserRepository $users, ChatRepository $chats, MessageRepository $messages, MessageTransformer $transformer) {
        $this->users = $users;
        $this->chats = $chats;
        $this->messages = $messages;
        $this->transformer = $transformer;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
//        $this->validateRequest($request);
        
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        $message = $this->findMessageOrFail($request->messageId, true);
        if(!$message->chat()->participants()->contains($requester)) {
            throw new \App\Application\Exceptions\ForbiddenException(228, 'No rights');
        }

        return new GetMessageResponse($this->transformer->transform($requester, $message));
    }
    
    function validate(GetRequest $request): void {
        PostParamsValidator::validateCommentsCountParam($request->commentsCount);
        PostParamsValidator::validateCommentsOrderParam($request->commentsOrder, ['asc', 'desc']);
        PostParamsValidator::validateCommentsTypeParam($request->commentsType);
    }
}
