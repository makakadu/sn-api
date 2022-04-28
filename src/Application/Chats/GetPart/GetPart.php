<?php
declare(strict_types=1);
namespace App\Application\Chats\GetPart;

use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Application\GetRequestParamsValidator;
use App\Domain\Model\Chats\ChatRepository;
use App\DataTransformer\Chats\ChatTransformer;
use App\Domain\Model\Chats\MessageRepository;
use Doctrine\Common\Collections\Criteria;

class GetPart implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Chats\ChatAppService;
    
    private ChatTransformer $chatTransformer;
    
    public function __construct(UserRepository $users, ChatRepository $chats, MessageRepository $messages, ChatTransformer $chatTransformer) {
        $this->users = $users;
        $this->chats = $chats;
        $this->messages = $messages;
        $this->chatTransformer = $chatTransformer;
    }
    
    public function execute2(BaseRequest $request): GetPartResponse {
        if($request->onlyUnread) {
            exit();
        }
        $requester = $this->findRequesterOrFail($request->requesterId);
        
        $user = $this->findUserOrFail($request->userId, true, null);
        
        if(!$requester->equals($user)) {
           throw new \App\Application\Exceptions\ForbiddenException(228, "No rights"); 
        }
        exit();
        $cursor = null;
        $count = !is_null($request->count) ? (int)$request->count : 20;
        $count++;

        $chats = $this->chats->getPartOfUser2(
            $user, $request->interlocutorId, $request->type
        );
        
        if($request->hideEmpty) {
            $nonEmptyChats = [];
            foreach($chats as $chat) {
                $currentParticipant = null;
                foreach($chat->participants() as $participant) {
                    if($participant->user()->equals($user)) {
                        $currentParticipant = $participant;
                        break;
                    }
                }
                $criteria = Criteria::create();
                $criteria
                    ->where(Criteria::expr()->eq("deletedForAll", false))
                    ->setMaxResults(1)
                    ->orderBy(array('id' => Criteria::DESC));

                $lastActiveMessage = $currentParticipant->messages()->matching($criteria)->first();
                if($lastActiveMessage) {
                    $chat->sortValue = $lastActiveMessage->id();
                    $nonEmptyChats[] = $chat;
                }
            }
            $chats = $nonEmptyChats;
        }
        else {
            foreach($chats as $chat) {
                $currentParticipant = null;
                foreach($chat->participants() as $participant) {
                    if($participant->user()->equals($user)) {
                        $currentParticipant = $participant;
                        break;
                    }
                }
                $criteria = Criteria::create();
                $criteria
                    ->where(Criteria::expr()->eq("deletedForAll", false))
                    ->setMaxResults(1)
                    ->orderBy(array('id' => Criteria::DESC));

                $lastActiveMessage = $currentParticipant->messages()->matching($criteria)->first();
                if($lastActiveMessage) {
                    $chat->sortValue = $lastActiveMessage->id();
                } else {
                    $chat->sortValue = $chat->id();
                }
            }
        }
        

        if($request->onlyUnread) {
            $unreadChats = [];
            foreach($chats as $chat) {
                $currentParticipant = null;
                foreach($chat->participants() as $participant) {
                    if($participant->user()->equals($user)) {
                        $currentParticipant = $participant;
                        break;
                    }
                }
                $lastReadMessageId = $currentParticipant->lastReadMessageId();

                $criteria = Criteria::create();
                $criteria
                    ->where(Criteria::expr()->eq("deletedForAll", 0))
                    ->andWhere(Criteria::expr()->neq("creatorId", $currentParticipant->user()->id()))
                    ->setMaxResults(1)
                    ->orderBy(array('id' => Criteria::DESC));
                
                $lastNotOwnActiveMessage = $currentParticipant->messages()->matching($criteria)->first();

                if($lastReadMessageId && $lastNotOwnActiveMessage && ($lastReadMessageId < $lastNotOwnActiveMessage->id())) {
                    $unreadChats[] = $chat;
                } else if(!$lastReadMessageId && $lastNotOwnActiveMessage) {
                    $unreadChats[] = $chat;
                } else {
                    continue;
                }
            }
            $chats = $unreadChats;
        }
        $allCount = count($chats);
        
        usort($chats, function ($x, $y) {
            if ($x->sortValue === $y->sortValue) {
                return 0;
            }
            return $x->sortValue > $y->sortValue ? -1 : 1;
        });
        
        if($request->cursor) {
            $index = null;
            for($i = 0;$i < count($chats);$i++) {
                if($chats[$i]->sortValue <= $request->cursor) {
                    $index = $i;
                    break;
                }
            }
            $chats = array_slice($chats, $index);
        }
        
        
        if(count($chats) > $count) {
            $chats = array_slice($chats, 0, $count);
        }

        if((count($chats) - ($count - 1)) === 1) {
            $cursor = $chats[count($chats) -1]->sortValue;
            array_pop($chats);
        }
        $messagesCount = !is_null($request->messagesCount)
            ? (int)$request->messagesCount : 10;
        
        $fields = is_null($request->fields) ? [] : explode(',', $request->fields);
        
        return new GetPartResponse(
            $this->chatTransformer->transformMultiple($requester, $chats, $messagesCount, $fields),
            $cursor,
            $allCount
       );
    }
    
    public function execute(BaseRequest $request): GetPartResponse {
        $requester = $this->findRequesterOrFail($request->requesterId);
        
        $user = $this->findUserOrFail($request->userId, true, null);
        
        if(!$requester->equals($user)) {
           throw new \App\Application\Exceptions\ForbiddenException(228, "No rights"); 
        }
        $cursor = null;
        $count = !is_null($request->count) ? (int)$request->count : 20;
        
        $chats = $this->chats->getChatsOfUserTest(
            $user,
            $request->interlocutorId,
            $request->cursor,
            $count +1,
            $request->type,
            (bool)$request->onlyUnread
        );

//        if(count($chats) > $count) {
//            $chats = array_slice($chats, 0, $count);
//        }
        if((count($chats) - $count) === 1) {
            $cursor = $chats[count($chats) -1]->sortValue;
            array_pop($chats);
        }
        $messagesCount = !is_null($request->messagesCount)
            ? (int)$request->messagesCount : 0;
        
        $fields = is_null($request->fields) ? [] : explode(',', $request->fields);
        
        return new GetPartResponse(
            $this->chatTransformer->transformMultiple($requester, $chats, $messagesCount, $fields),
            $cursor,
            1234
       );
    }
}
