<?php
declare(strict_types=1);
namespace App\Domain\Model\Chats;

interface ActionsEnum {
    const CREATE_CHAT = 'create-chat';
    const CREATE_MESSAGE = 'create-message';
    const DELETE_MESSAGE = 'delete-message';
    const DELETE_MESSAGE_FOR_ALL = 'delete-message-for-all';
    const READ_MESSAGE = 'read-message';
    const DELETE_HISTORY = 'delete-history';
}
