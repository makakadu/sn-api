<?php
declare(strict_types=1);
namespace App\Domain\Model\Chats\Actions;

use App\Domain\Model\Chats\Action;
use App\Domain\Model\Chats\ActionsEnum;
use App\Domain\Model\Chats\ActionVisitor;
use App\Domain\Model\Chats\Chat;
use App\Domain\Model\Users\User\User;

class DeleteHistoryAction extends Action {

    public function __construct(Chat $chat, User $initiator) {
        parent::__construct($chat, $initiator->id(), ActionsEnum::DELETE_MESSAGE);
    }

    public function acceptActionVisitor(ActionVisitor $visitor) {
        return $visitor->visitDeleteHistoryAction($this);
    }
}