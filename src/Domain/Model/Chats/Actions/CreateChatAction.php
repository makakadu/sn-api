<?php
declare(strict_types=1);
namespace App\Domain\Model\Chats\Actions;

use App\Domain\Model\Chats\Action;
use App\Domain\Model\Chats\ActionsEnum;
use App\Domain\Model\Chats\ActionVisitor;
use App\Domain\Model\Chats\Chat;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Chats\Message;

class CreateChatAction extends Action {
    private Message $firstMessage;
    
    public function __construct(
        Chat $chat,
        User $initiator,
        Message $firstMessage
    ) {
        parent::__construct($chat, $initiator->id(), ActionsEnum::CREATE_CHAT);
        $this->firstMessage = $firstMessage;
    }
    
    public function acceptActionVisitor(ActionVisitor $visitor) {
        return $visitor->visitCreateChatAction($this);
    }
    
    function getFirstMessage(): Message {
        return $this->firstMessage;
    }
}
