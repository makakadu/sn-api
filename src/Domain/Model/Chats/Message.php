<?php
declare(strict_types=1);
namespace App\Domain\Model\Chats;

use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\EntityTrait;
use Ulid\Ulid;

class Message {
    use EntityTrait;
    private string $clientId;
    private User $creator;
    private string $text;
    private Chat $chat;
    private ?string $chatId = null;
    private bool $deletedForAll;
    private array $deletedFor;
//    private Collection $deletedFor;
    public string $creatorId;
    public bool $isEdited = false;
    private ?self $replied;

    function __construct(string $clientId, User $creator, Chat $chat, string $text, ?self $replied) {
        $this->id = (string)Ulid::generate(true);
        $this->clientId = $clientId;
        $this->creator = $creator;
        $this->creatorId = $creator->id();
        $this->chat = $chat;
        $this->chatId = $chat->id();
        $this->text = $text;
        $this->deletedForAll = false;
        $this->deletedFor = [];//new ArrayCollection();
        $this->createdAt = new \DateTime('now');
        $this->isRead = false;
        if($replied && $replied->chatId !== $this->chatId) {
            throw new \InvalidArgumentException('Message from another chat');
        }
        $this->replied = $replied;
//        echo $this->key;exit();
    }
    
    public function getIsEdited(): bool {
        return $this->isEdited;
    }

    function getReplied(): ?self {
        return $this->replied;
    }
    
    function chatId(): string {
        return $this->chatId;
    }
    
    function clientId(): string {
        return $this->clientId;
    }
    
    function creatorId(): string {
        return $this->creatorId;
    }
    
    function getId(): string {
        return $this->id;
    }
    
    function read(): void {
        $this->isRead = true;
    }
    
    function isRead(): bool {
        return $this->isRead;
    }
    
    function changeText(string $text): void {
        $this->text = $text;
        $this->isEdited = true;
    }

    function creator(): User { return $this->creator; }
    function text(): string { return $this->text; }
    function creationDate(): \DateTime { return $this->creationDate; }
    function chat(): Chat {
        return $this->chat;
    }
    
    function deleteForAll(User $initiator): void {
        if(!$this->creator->equals($initiator)) {
            throw new \App\Application\Exceptions\ForbiddenException(228, 'No rights');
        }
        $this->deletedForAll = true;
        $this->chat->addAction(new Actions\DeleteMessageForAllAction(
            $this->chat,
            $initiator,
            $this->id,
            $this->clientId,
            $this->creatorId
        ));
    }
    
//    function deleteForUser(User $user): void {
//        $currentParticipant = null;
//        foreach($this->participants as $participant) {
//            if($participant->user()->equals($user)) {
//                $currentParticipant = $participant;
//                break;
//            }
//        }
//        if(!$currentParticipant) {
//            throw new DomainExceptionAlt([
//                'message' => "User {$user->id()} is not a participant of chat {$this->chat->id()}"
//            ]);
//        }
////        if(!$this->chat->participants()->contains($user)) {
////            throw new DomainExceptionAlt([
////                'message' => "User {$user->id()} is not a participant of chat {$this->chat->id()}"
////            ]);
////        }
////        $this->deletedFor[] = $user->id();
//        $this->deletedFor->add($currentParticipant);
//    }
    
    public function getDeletedForAll(): bool {
        return $this->deletedForAll;
    }

    public function getDeletedFor(): Collection {
        return $this->deletedFor;
    }


}
