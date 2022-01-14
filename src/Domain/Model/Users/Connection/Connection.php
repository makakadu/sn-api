<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Connection;

use App\Domain\Model\DomainException;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;
use Ulid\Ulid;

/* В вк можно восстанить дружбу, если случайно удалил. А в фейсбуке просто просят подтверждение удаления, если вдруг случайно нажал. Мне не хочется играться с восстановлением, 
 * поэтому я его не буду добавлять
 */
class Connection {
    use EntityTrait;
    
    private bool $isAccepted = false;
    
    protected User $user1;
    protected User $user2;
    
    protected string $user1Id; // тот, кто предложил
    protected string $user2Id; // тот, кому предложили
    protected string $uniqueKey; // Оба ID не могут обеспечить уникальность, потому что есть 2 комбинации $user1Id и $user2Id, то есть, например, возможно существование
    // двух Connection, в одном $user1Id === 123 и $user2Id === 456, а во втором $user1Id === 456 и $user2Id === 123, оба connections соединяют одних и тех же пользователей
    // но при в разной последовательности. А я хочу чтобы бы мог существовать только один Connection который соеднияет пользователя 123 с пользователем 456
    
    private ?\DateTime $acceptedAt = null;
    private string $deletedAt = "";
    private ?string $deletedBy = null;
    
    function __construct(User $user1, User $user2) {
        if($user1->equals($user2)) {
            throw new DomainException('Cannot create connection with himself');
        }
        $this->id = (string)Ulid::generate(true);
        $this->user1 = $user1;
        $this->user2 = $user2;
        $this->user1Id = $user1->id();
        $this->user2Id = $user2->id();
        $this->uniqueKey = $user1->id() < $user2->id()
            ? ($user1->id() . $user2->id()) : ($user2->id() . $user1->id());

        $this->createdAt = new \DateTime("now");
    }
    
    public function getUser1(): User {
        return $this->user1;
    }

    public function getUser2(): User {
        return $this->user2;
    }
    
    function id(): string {
        return $this->id;
    }
    
    function initiatorId(): string {
        return $this->user1Id;
    }
    
    function targetId(): string {
        return $this->user2Id;
    }

    function accept(User $user): void {
        if($this->isAccepted) {
            throw new DomainException("Connection {$this->id()} is already accepted");
        }
        if($user->id() !== $this->user2Id) {
            throw new DomainException("Cannot accept connection, only the user who was offered the connection can accept the connection");
        }
        $this->acceptedAt = new \DateTime("now");
        $this->isAccepted = true;
    }
    
    function delete(User $user): void {
        if($user->id() !== $this->user1Id && $user->id() !== $this->user2Id) {
            throw new DomainException("No rights to delete");
        }
        $this->deletedAt = (string)(new \DateTime('now'))->getTimestamp();
        $this->deletedBy = $user->id();
    }
    
    function isAccepted(): bool {
        return $this->isAccepted;
    }
    
    function __toString(): string {
        return (string)$this->id();
    }
}
