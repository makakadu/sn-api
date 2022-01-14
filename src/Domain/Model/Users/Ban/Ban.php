<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Ban;

use App\Domain\Model\Users\User\User;

class Ban {
    use \App\Domain\Model\EntityTrait;
    
    const MAX_DURATION = 525600;
    const MESSAGE_MAX_LENGTH = 500;
    
    public User $owner;
    public User $banned;
//    public ?\DateTime $start;
//    public ?\DateTime $end;
//    private ?string $message;
//    private bool $canceled = null;
//    private ?\DateTime $canceledAt = null;
//    private string $archivedAt = "";
            
    function __construct(User $creator, User $banned) {
        if($creator->equals($banned)) {
            throw new \LogicException("Cannot ban himself");
        }
        $this->id = (string) \Ulid\Ulid::generate(true);
        $this->owner = $creator;
        $this->banned = $banned;
        
//        $this->message = $message;
//        
//        $this->start = new \DateTime('now');
//        
//        if(!$minutes) {
//            $this->end = null;
//        } else {
//            $this->end = new \DateTime('now');
//            $this->end->add(new \DateInterval('PT' . $minutes . 'M'));
//        }
        $this->createdAt = new \DateTime('now');
    }
//    
//    function cancel(): void {
//        $this->canceled = true;
//        $this->canceledAt = new \DateTime('now');
//        $this->archivedAt = (string)(new \DateTime('now'))->getTimestamp();
//    }
//    
//    function archive(): void {
//        if($this->archivedAt === "") {
//            $this->archivedAt = (string)(new \DateTime('now'))->getTimestamp();
//        }
//    }
    
    /*
     * Возможно стоит убрать изменение бана и оставить только создание. Изменение бана нелогично, если старт бана меняется на new \DateTime('now').
     */
//    function changeDuration(?int $minutes): void {
//        if($this->end < new \DateTime('now')) {
//            throw new \App\Domain\Model\DomainException("Ban cannot be modified because it is over");
//        }
//        $this->start = new \DateTime('now');
//        
//        if(!$minutes) {
//            $this->end = null;
//        } else {
//            $this->end = new \DateTime('now');
//            $this->end->add(new \DateInterval('PT' . $minutes . 'M'));
//        }
//    }
    
    function creator(): User {
        return $this->owner;
    }
    
    function banned(): User {
        return $this->banned;
    }
//
//    function start(): ?\DateTime {
//        return $this->start;
//    }
//
//    function end(): ?\DateTime {
//        return $this->end;
//    }

    function message(): ?string {
        return $this->message;
    }


}
