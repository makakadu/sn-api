<?php

declare(strict_types=1);

namespace App\Domain\Model\Dialogue;

use App\Domain\Model\Identity\User\User;

class Message {
    public $version;
    private int $id;
    private User $creator;
    private string $text;
    private \DateTime $creationDate;
    private Chat $chat;
    private ?\DateTime $deletedAt;

    function __construct(User $creator, Chat $chat, string $text) {
        $this->id = \uniqid();
        $this->creator = $creator;
        $this->chat = $chat;
        $this->text = $text;
        $this->creationDate = new \DateTime('now');
    }
    
    function changeText(string $text): void {
        $this->text = $text;
    }

    function id(): int { return $this->id; }
    function creator(): User { return $this->creator; }
    function text(): string { return $this->text; }
    function creationDate(): \DateTime { return $this->creationDate; }
    function chat(): Chat {
        return $this->chat;
    }
    
    function changeDeletedAt(?string $value): void {
        $exceptionMessage = "Invalid value. Method accepts string with value 'now' or integer timestamp or null";
//        if(is_string($value) && $value !== 'now') {
//            throw new \InvalidArgumentException($exceptionMessage);
//        } else if(is_integer($value)) {
//            $dateTime = new \DateTime('now');
//            $dateTime->setTimestamp($value);
//            $this->deletedAt = $dateTime;
//        } else if($value === null){
//            $this->deletedAt = $deletedAt;
//        } else {
//            throw new \InvalidArgumentException($exceptionMessage);
//        }
        if(is_string($value) && $value !== 'now') {
            throw new \InvalidArgumentException("Method accepts only values 'now' and null");
        } else if($value === 'now') {
            $this->deletedAt = new \DateTime($value);
        } else {
            $this->deletedAt = null;
        }
    }
}
