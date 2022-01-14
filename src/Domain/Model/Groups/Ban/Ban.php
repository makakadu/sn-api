<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Ban;

use App\Domain\Model\Groups\Group\Group;
use App\Domain\Model\Users\User\User;

class Ban {
    use \App\Domain\Model\EntityTrait;
    use \App\Domain\Model\Groups\GroupEntity;
    
    protected User $initiator;
    protected User $banned;
    protected ?Group $_group;
    
    private string $reason;
    private ?string $message;
    
    private ?int $minutes;
    private ?\DateTime $end = null; // Когда $end меньше, чем текущее время, бан становится неактивным и не берётся в учёт. 
    // Возможно просроченные баны даже не стоит удалять, может быть они понадобятся в будущем
    
    private string $deletedAt = "";
    
//    private bool $isDeleted = false;
//    private ?\DateTime $deletedAt = null;
//    private string $uniqueValue = "active"; // Только у одного бана может быть такое значение в БД.

    function __construct(Group $group, User $initiator, User $banned, ?int $minutes, string $reason, ?string $message) {
        $this->id = (string)\Ulid\Ulid::generate(true);
        
        $this->_group = $group;
        $this->initiator = $initiator;
        $this->banned = $banned;

        if($minutes) {
            $time = new \DateTime('now');
            $time->add(new \DateInterval('PT' . $minutes . 'M'));
            $this->end = $time;
        }
        $this->minutes = $minutes;
        $this->createdAt = new \DateTime("now");
        
        $this->reason = $reason;
        $this->message = $message;
    }
    
    function edit(?int $minutes, string $reason, ?string $message): void {
        if($this->end < new \DateTime('now')) {
            throw new \App\Domain\Model\DomainException("Cannot edit expired ban");
        }
        $this->minutes = $minutes;
        if($minutes) {
            $time = new \DateTime('now');
            $time->add(new \DateInterval('PT' . $minutes . 'M'));
            $this->end = $time;
        } else {
            $this->end = null;
        }
        $this->reason = $reason;
        $this->message = $message;
    }
    
    function getEnd(): ?\DateTime {
        return $this->end;
    }
    
    function delete(): void {
        $this->deletedAt = (string)(new \DateTime('now'))->getTimestamp();
    }
    
    function deletedAt(): string {
        return $this->deletedAt;
    }
//    
//    function delete(): void {
//        $this->isDeleted = true;
//        if(!$this->deletedAt) {
//            $this->deletedAt = new \DateTime('now');
//            $this->uniqueValue = $this->id; // Когда это свойство не имеет значения ""(пустой строки), то значит можно создавать новый бан
//        }
//    }
    
}
