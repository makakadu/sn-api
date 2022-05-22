<?php
declare(strict_types=1);
namespace App\Domain\Model\Chats\Actions;

use App\Domain\Model\Chats\Action;
use App\Domain\Model\Chats\ActionsEnum;
use App\Domain\Model\Chats\ActionVisitor;
use App\Domain\Model\Chats\Chat;
use App\Domain\Model\Users\User\User;

class ReadMessageAction extends Action {
    private int $unreadMessagesCount; // Конечно это значение станет неактуальным очень быстро, но оно всё равно нужно
    private string $messageId;
    private string $messageClientId;
    private string $messageCreatorId;
    
    public function __construct(
        Chat $chat, User $initiator,
        string $messageId,
        string $messageClientId,
        string $messageCreatorId,
        int $unreadMessagesCount
    ) {
        parent::__construct($chat, $initiator->id(), ActionsEnum::READ_MESSAGE);
        $this->unreadMessagesCount = $unreadMessagesCount;
        $this->messageId = $messageId;
        $this->messageCreatorId = $messageCreatorId;
        $this->messageClientId = $messageClientId;
    }
    
    function getUnreadMessagesCount(): int {
        return $this->unreadMessagesCount;
    }
    
    public function getMessageId(): string {
        return $this->messageId;
    }

    public function getMessageClientId(): string {
        return $this->messageClientId;
    }

    public function getMessageCreatorId(): string {
        return $this->messageCreatorId;
    }

    
    public function acceptActionVisitor(ActionVisitor $visitor) {
        return $visitor->visitReadMessageAction($this);
    }

}
