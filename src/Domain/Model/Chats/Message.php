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
    private bool $deletedForAll;
    private array $deletedFor;
//    private Collection $deletedFor;
    public string $creatorId;

    function __construct(string $clientId, User $creator, Chat $chat, string $text) {
        $this->id = (string)Ulid::generate(true);
        $this->clientId = $clientId;
        $this->creator = $creator;
        $this->creatorId = $creator->id();
        $this->chat = $chat;
        $this->text = $text;
        $this->deletedForAll = false;
        $this->deletedFor = [];//new ArrayCollection();
        $this->createdAt = new \DateTime('now');
        $this->isRead = false;
//        echo $this->key;exit();
    }
    
    function clientId(): string {
        return $this->clientId;
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
        $this->chat->addAction($initiator, Action::DELETE_MESSAGE_FOR_ALL, $this->id, $this->clientId, $this->creatorId);
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
