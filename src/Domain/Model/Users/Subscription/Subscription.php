<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Subscription;

use App\Domain\Model\DomainException;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;
use Ulid\Ulid;

class Subscription {
    use EntityTrait;
    
    private string $userId;
    private string $subscriberId;
    private User $user;
    private User $subscriber;

    private ?int $pauseDurationInDays = null; // Я добавил это свойство, чтобы это больше подходилдо для REST API
    private ?\DateTime $pauseStart = null;
    private ?\DateTime $pauseEnd = null;
    
    protected string $uniqueKey;
    
//    private bool $isDeleted = false;
//    private ?\DateTime $deletedAt;
    
    function __construct(User $subscriber, User $user) {
        if($user->equals($subscriber)) {
            throw new \InvalidArgumentException('Cannot subscribe to himself');
        }
        $this->id = (string)Ulid::generate(true);
        
        $this->user = $user;
        $this->userId = $user->id();
        $this->subscriber = $subscriber;
        $this->subscriberId = $subscriber->id();
        
        $this->uniqueKey = $user->id() . '_' . $subscriber->id();
        
//        $this->pauseStart = new \DateTime("now");
//        $this->pauseEnd = new \DateTime("now");
        $this->createdAt = new \DateTime("now");
    }
    
    function subscriber(): User {
        return $this->subscriber;
    }
    
    function user(): User {
        return $this->user;
    }
    
    function pauseDurationInDays(): ?int {
        return $this->pauseDurationInDays;
    }
    
    public function pauseStart(): ?\DateTime {
        return $this->pauseStart;
    }

    public function pauseEnd(): ?\DateTime {
        return $this->pauseEnd;
    }


//    
//    function delete(): void {
////        if($this->deletedAt) { // Не знаю стоит ли выбрасывать это исключение, мне кажется, что от него больше проблем, чем выгоды, если честно, я выгоды не вижу, вообще
////            throw new DomainException("Subscription already is softly deleted");
////        } 
//        $this->pauseEnd = new \DateTime("now"); // Мне кажется стоит сбросить muteEnd, потому что если Subscription будет восстановлена, то она будет считать как новой
//        // подпиской, вряд ли пользователь будет доволен, если он заново подпишется и подписка будет замьючена
//        $this->isDeleted = true;
//        $this->deletedAt = new \DateTime("now");
//    }
//    
//    function restore(): void {
////        if(!$this->deletedAt) {
////            throw new DomainException("Cannot restoreSubscription already is NOT soflty deleted");
////        }
//        $this->isDeleted = false;
//        $this->deletedAt = null;
//    }
    
    function pause(int $days): void {
//        if($this->isDeleted) {
//            throw new DomainException("Cannot pause subscription if it is soflty deleted");
//        }
        $this->pauseStart = new \DateTime("now");
        $this->pauseEnd = (new \DateTime("now"))->modify("+$days day");
        $this->pauseDurationInDays = $days;
    }
    
    function unpause(): void {
//        if($this->isDeleted) {
//            throw new DomainException("Cannot unpause subscription if it is soflty deleted");
//        }
        $this->pauseStart = null;
        $this->pauseEnd = null;
        $this->pauseDurationInDays = null;
    }
}
