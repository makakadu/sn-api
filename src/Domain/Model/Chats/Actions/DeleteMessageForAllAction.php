<?php
declare(strict_types=1);
namespace App\Domain\Model\Chats\Actions;

use App\Domain\Model\Chats\Action;
use App\Domain\Model\Chats\ActionsEnum;
use App\Domain\Model\Chats\ActionVisitor;
use App\Domain\Model\Chats\Chat;
use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Criteria;

class DeleteMessageForAllAction extends Action {
    private string $messageId;
    private string $messageClientId;
    private string $messageCreatorId;
    
    public function __construct(
        Chat $chat,
        User $initiator,
        string $messageId,
        string $messageClientId,
        string $messageCreatorId
    ) {
        parent::__construct($chat, $initiator->id(), ActionsEnum::DELETE_MESSAGE_FOR_ALL);
        $this->messageId = $messageId;
        $this->messageClientId = $messageClientId;
        $this->messageCreatorId = $messageCreatorId;
        $this->chat = $chat;
    }

    public function acceptActionVisitor(ActionVisitor $visitor) {
        return $visitor->visitDeleteMessageForAllAction($this);
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
}
