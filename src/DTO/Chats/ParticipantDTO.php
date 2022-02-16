<?php
declare(strict_types=1);
namespace App\DTO\Chats;

class ParticipantDTO {
    
    public string $id;
    public string $userId;
    public string $username;
    public string $firstName;
    public string $lastName;
    public ?string $lastReadMesssageId;
    public ?MessageDTO $lastMessage;
    public array $messages;
    public ?string $messageCursor;
    
    public function __construct(
        string $id,
        string $userId,
        string $username, 
        string $firstName, 
        string $lastName, 
        ?string $lastReadMesssageId,
        ?MessageDTO $lastMessage,
        array $messages,
        ?string $messageCursor
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->username = $username;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->lastReadMesssageId = $lastReadMesssageId;
        $this->lastMessage = $lastMessage;
        $this->messages = $messages;
        $this->messageCursor = $messageCursor;
    }

}
