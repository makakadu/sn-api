<?php
declare(strict_types=1);
namespace App\DataTransformer\Chats;

use App\Domain\Model\Chats\Actions\DeleteMessageAction;
use App\DTO\Chats\ActionDTO;
use App\Domain\Model\Chats\Actions\DeleteMessageForAllAction;
use App\Domain\Model\Chats\Actions\CreateMessageAction;
use App\Domain\Model\Chats\Actions\ReadMessageAction;
use App\Domain\Model\Chats\ActionVisitor;
use App\Domain\Model\Chats\Action;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Chats\Actions\CreateChatAction;
use App\DataTransformer\Chats\ChatTransformer;
use App\Domain\Model\Chats\Actions\DeleteHistoryAction;

/**
 * @implements ActionVisitor <ActionDTO>
 */
class ActionTransformer implements ActionVisitor {
    
    private User $requester;
    
    public function __construct(User $requester) {
        $this->requester = $requester;
    }
    
    /**
     * @param array<Action> $actions
     * @return array<ActionDTO>
     */
    function transformMultiple(array $actions): array {
        $dtos = [];
        foreach($actions as $action) {
            $dtos[] = $action->acceptActionVisitor($this);
        }
        return $dtos;
    }
    
    function transform(Action $action): ActionDTO {
        return $action->acceptActionVisitor($this);
    }
    
    /**
     * @return ActionDTO
     */
    function visitDeleteMessageAction(DeleteMessageAction $action) {
        $lastMessage = $action->getChat()->getLastMessageOfUser($this->requester);
        
        return new ActionDTO(
            $action->getType(),
            $action->getChatId(),
            $action->getChatClientId(),
            $action->getInitiatorId(),
            $action->getCreatedAt()->getTimestamp(),
            [
                'messageId' => $action->getMessageId(),
                'messageClientId' => $action->getMessageClientId(),
                'messageCreatorId' => $action->getMessageCreatorId(),
                'lastMessage' => $lastMessage
            ]
        );
    }
    
    /**
     * @return ActionDTO
     */
    function visitDeleteMessageForAllAction(DeleteMessageForAllAction $action) {
        $lastMessage = $action->getChat()->getLastMessageOfUser($this->requester);
        
        return new ActionDTO(
            $action->getType(),
            $action->getChatId(),
            $action->getChatClientId(),
            $action->getInitiatorId(),
            $action->getCreatedAt()->getTimestamp(),
            [
                'messageId' => $action->getMessageId(),
                'messageClientId' => $action->getMessageClientId(),
                'messageCreatorId' => $action->getMessageCreatorId(),
                'lastMessage' => $lastMessage
            ]
        );
    }
    
    /**
     * @return ActionDTO
     */
    function visitCreateMessageAction(CreateMessageAction $action) {
        $messageDTO = (new MessageTransformer())->transform($this->requester, $action->getMessage());
        $chatDTO = (new ChatTransformer())->transformPartial(
            $this->requester, $action->getChat(), 0,
            ['lastReadMessageId', 'participants', 'type', 'unreadMessagesCount', 'startedBy', 'createdAt']
        );
        return new ActionDTO(
            $action->getType(),
            $action->getChatId(),
            $action->getChatClientId(),
            $action->getInitiatorId(),
            $action->getCreatedAt()->getTimestamp(),
            [
                'message' => $messageDTO,
                'chat' => $chatDTO
            ]
        );
    }
    
    /**
     * @return ActionDTO
     */
    function visitReadMessageAction(ReadMessageAction $action) {
        $extraFields = [];
        if($this->requester->id() === $action->getInitiatorId()) {
            $extraFields['unreadMessagesCount'] = $action->getUnreadMessagesCount();
        }
        $extraFields['messageId'] = $action->getMessageId();
        $extraFields['messageClientId'] = $action->getMessageClientId();
        $extraFields['messageCreatorId'] = $action->getMessageCreatorId();
        
        return new ActionDTO(
            $action->getType(),
            $action->getChatId(),
            $action->getChatClientId(),
            $action->getInitiatorId(),
            $action->getCreatedAt()->getTimestamp(),
            $extraFields
        );
    }
    
    /**
     * @return ActionDTO
     */
    public function visitCreateChatAction(CreateChatAction $action) {
        $firstMessageDTO = (new MessageTransformer())->transform($this->requester, $action->getFirstMessage());
        $chatDTO = (new ChatTransformer())->transform(
            $this->requester, $action->getChat(), 0
        );
        return new ActionDTO(
            $action->getType(),
            $action->getChatId(),
            $action->getChatClientId(),
            $action->getInitiatorId(),
            $action->getCreatedAt()->getTimestamp(),
            [
                'chat' => $chatDTO,
                'firstMessage' => $firstMessageDTO
            ]
        );
    }
    
    /**
     * @return ActionDTO
     */
    public function visitDeleteHistoryAction(DeleteHistoryAction $action) {
        return new ActionDTO(
            $action->getType(),
            $action->getChatId(),
            $action->getChatClientId(),
            $action->getInitiatorId(),
            $action->getCreatedAt()->getTimestamp()
        );
    }

}
