<?php
declare(strict_types=1);
namespace App\Domain\Model\Chats\Actions;

use App\Domain\Model\Chats\Action;
use App\Domain\Model\Chats\ActionsEnum;
use App\Domain\Model\Chats\ActionVisitor;
use App\Domain\Model\Chats\Chat;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Chats\Message;

class CreateMessageAction extends Action {
    private Message $message;
    
    public function __construct(
        Chat $chat,
        User $initiator,
        Message $message
    ) {
        parent::__construct($chat, $initiator->id(), ActionsEnum::CREATE_MESSAGE);
        $this->message = $message;
    }
    
    public function acceptActionVisitor(ActionVisitor $visitor) {
        return $visitor->visitCreateMessageAction($this);
    }
    
    function getMessage(): Message {
        return $this->message;
    }
}
