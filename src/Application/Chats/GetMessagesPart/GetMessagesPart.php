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
        
        $participantMessages = null;
        foreach($chat->participants() as $participant) {
            if($participant->user()->equals($requester)) {
                $participantMessages = $participant->messages();
                break;
            }
        }
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq("deletedForAll", 0));
        if($request->cursor) {
            $criteria->andWhere(Criteria::expr()->lte('id', $request->cursor));
        }
        $criteria->setMaxResults($count + 1);
        $criteria->orderBy(array('id' => Criteria::DESC));

        $messages = $participantMessages->matching($criteria)->toArray();

        $cursor = null;
        if((count($messages) - $count) === 1) {
            $cursor = $messages[count($messages) -1]->id();
            array_pop($messages);
        }
        
        return new GetMessagesPartResponse(
            $this->trans->transformMultiple($requester, $messages),
            $cursor
       );
    }
}
