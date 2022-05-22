<?php
declare(strict_types=1);
namespace App\Application\Chats\GetMessagesPart;

use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Chats\ChatRepository;
use App\Domain\Model\Chats\MessageRepository;
use App\DataTransformer\Chats\MessageTransformer;
use Doctrine\Common\Collections\Criteria;

class GetMessagesPart implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Chats\ChatAppService;
    
    private MessageTransformer $trans;
    
    public function __construct(UserRepository $users, ChatRepository $chats, MessageRepository $messages, MessageTransformer $trans) {
        $this->users = $users;
        $this->chats = $chats;
        $this->messages = $messages;
        $this->trans = $trans;
    }
    
    public function execute(BaseRequest $request): GetMessagesPartResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $chat = $this->findChatOrFail($request->chatId, true, null);
        $count = $request->count ? (int)$request->count : 20;
        $order = $request->order ? $request->order : 'DESC';
        $cursor = $request->cursor;
        
        $participant = $chat->getParticipantByUserId($requester->id());
        $messages = $participant->getMessages($count + 1, $order, $cursor);

        $nextCursor = null;
        $prevCursor = null;
        $messagesNumberMoreThanCount = (count($messages) - $count) === 1;
        if($cursor && $messagesNumberMoreThanCount) {
            $nextCursor = $messages->last()->id();
            $prevMessage = $this->getPrev($participant, $messages->first()->id(), $order);
            if($prevMessage) { $prevCursor = $prevMessage->id(); }
            $messages = $messages->toArray();
            array_pop($messages);
        }
        elseif($cursor && !$messagesNumberMoreThanCount) {
            $prevMessage = $this->getPrev($participant, $messages->first()->id(), $order);
            if($prevMessage) { $prevCursor = $prevMessage->id(); }
            $messages = $messages->toArray();
        }
        elseif(!$cursor && $messagesNumberMoreThanCount) {
            $nextCursor = $messages->last()->id();
            $messages = $messages->toArray();
            array_pop($messages);
        }
//        
//        $nextMessageCursor = null;
//        if((count($messages) - $count) === 1) {
//            $nextMessageCursor = $messages[count($messages) -1]->id();
//            array_pop($messages);
//        }
        return new GetMessagesPartResponse(
            $this->trans->transformMultiple($requester, $messages),
            $prevCursor,
            $nextCursor,
       );
    }
    
    function getPrev(\App\Domain\Model\Chats\Participant $participant, string $messageId, string $order): ?\App\Domain\Model\Chats\Message {
        $oppositeOrder = $order === 'ASC' ? 'DESC' : 'ASC';
        $firstAndPrev = $participant->getMessages(2, $oppositeOrder, $messageId);
        if($firstAndPrev->count() === 2) {
            return $firstAndPrev->last();
        }
        return null;
    }
}
