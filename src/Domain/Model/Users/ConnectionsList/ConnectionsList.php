<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\ConnectionsList;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Users\Connection\Connection;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\DomainException;

class ConnectionsList {
    private string $id;
    private string $name;
    private User $user;
    /** @var Collection<string, Connection> $connections */
    private Collection $connections;
    private bool $isDefault;
    private \DateTime $createdAt;
    private bool $isDeleted = false;
    private ?\DateTime $deletedAt = null;
    
    function __construct(User $user, string $name, bool $isDefault = false) {
        $this->id = (string) \Ulid\Ulid::generate(true);
        $this->user = $user;
        $this->name = $name;
        $this->connections = new ArrayCollection();
        $this->isDefault = $isDefault;
        $this->createdAt = new \DateTime('now');
    }
    
    function addConnection(Connection $connection): void {
        if($this->user->id() !== $connection->initiatorId() && $this->user->id() !== $connection->targetId()) {
            throw new \LogicException("Cannot add connection to connections list if owner of list is not a participant of connection");
        }
        $this->connections->add($connection);
    }
    
    function removeConnection(string $id): void {
        $connection = $this->connections->get($id);
        if(!$connection) {
            throw new DomainException("There is no connection $id in connections list {$this->id}");
        }
        $this->connections->remove($id);
    }

    function contains(Connection $connection): bool {
        return $this->connections->contains($connection);
    }
    
    function id(): string {
        return $this->id;
    }
    
    /** @return ArrayCollection<string, Connection> */
    function connections(): ArrayCollection {
        /** @var ArrayCollection<string, Connection> $connections */
        $connections = $this->connections;
        return $connections;
    }
    
    function user(): User {
        return $this->user;
    }
    
    function delete(): void {
        $this->isDeleted = true;
    }
    
    function restore(): void {
        $this->isDeleted = false;
    }
    
    function changeName(string $name): void {
        $this->name = $name;
    }
}
