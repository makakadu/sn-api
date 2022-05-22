<?php
declare(strict_types=1);
namespace App\Domain\Model\Chats;

use App\Domain\Model\Chats\Actions\DeleteMessageAction;
use App\Domain\Model\Chats\Actions\DeleteMessageForAllAction;
use App\Domain\Model\Chats\Actions\CreateMessageAction;
use App\Domain\Model\Chats\Actions\ReadMessageAction;
use App\Domain\Model\Chats\Actions\CreateChatAction;
use App\Domain\Model\Chats\Actions\DeleteHistoryAction;

/**
 * @template T
 */
interface ActionVisitor {
    /**
     * @return T
     */
    function visitDeleteMessageAction(DeleteMessageAction $action);
    /**
     * @return T
     */
    function visitDeleteMessageForAllAction(DeleteMessageForAllAction $action);
    /**
     * @return T
     */
    function visitCreateMessageAction(CreateMessageAction $action);
    /**
     * @return T
     */
    function visitReadMessageAction(ReadMessageAction $action);
    /**
     * @return T
     */
    function visitCreateChatAction(CreateChatAction $action);
    /**
     * @return T
     */
    function visitDeleteHistoryAction(DeleteHistoryAction $action);
    
}
