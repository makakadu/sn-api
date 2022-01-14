<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Albums;

use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\ComplexPrivacySettingTrait;
use App\Domain\Model\Users\ComplexPrivacySetting;
use App\Domain\Model\Users\Connection\Connection;
use App\Domain\Model\Users\ConnectionsList\ConnectionsList;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\DomainException;

class PrivacySetting implements ComplexPrivacySetting {
    use EntityTrait;
    use ComplexPrivacySettingTrait;
    
    private string $albumId;
    private string $name;
            
    /**
     * @param array<mixed> $lists
     */
    function __construct(Album $album, string $name, int $accessLevel, array $lists) {
        $this->failIfOutOfRange($accessLevel);
        $this->accessLevel = $accessLevel;
        $this->albumId = $album->id();
        $this->name = $name;
        $this->createdAt = new \DateTime("now");
        
        $allowedConnections = $lists['allowed_connections'];
        $unallowedConnections = $lists['unallowed_connections'];
        $allowedLists = $lists['allowed_lists'];
        $unallowedLists = $lists['unallowed_lists'];
        
        $this->ownerId = $album->user()->id();
        $user = $album->user();
        
        $this->failIfSameConnectionInBothLists($allowedConnections, $unallowedConnections);
        $this->failIfSameConnectionsListInBothLists($allowedLists, $unallowedLists);
        
        $this->failIfUserIsNotParticipantOfConnection($user, $allowedConnections);
        $this->failIfUserIsNotParticipantOfConnection($user, $unallowedConnections);
        $this->failIfUserIsNotOwnerOfConnectionsList($user, $allowedLists);
        $this->failIfUserIsNotOwnerOfConnectionsList($user, $unallowedLists);
        
        $this->verifyComplianceToAccessLevel(
            $accessLevel, $allowedConnections, $unallowedConnections, $allowedLists, $unallowedLists
        );
        $this->setLists($allowedConnections, $unallowedConnections, $allowedLists, $unallowedLists);
    }
    
    /**
     * @param array<Connection> $connections
     */
    function failIfUserIsNotParticipantOfConnection(User $user, array $connections): void {
        foreach ($connections as $conn) {
            if($user->id() !== $conn->initiatorId() && $user->id() !== $conn->targetId() ) {
                throw new DomainException("Incorrect privacy data, album creator ({$user->id()}) is not participant of connection {$conn->id()}");
            }
        }
    }
    
    /**
     * @param array<ConnectionsList> $connectionsLists
     */
    function failIfUserIsNotOwnerOfConnectionsList(User $user, array $connectionsLists): void {
        /** @var ConnectionsList $list */
        foreach ($connectionsLists as $list) {
            if(!$user->equals($list->user())) {
                throw new DomainException("Incorrect privacy data, album creator ({$user->id()}) is not owner of connections list {$list->id()}");
            }
        }
    }
    
    /**
     * @param array<mixed> $lists
     */
    function edit(int $accessLevel, array $lists): void {
        $this->failIfOutOfRange($accessLevel);
        $this->accessLevel = $accessLevel;

        $allowedConnections = $lists['allowed_connections'];
        $unallowedConnections = $lists['unallowed_connections'];
        $allowedLists = $lists['allowed_lists'];
        $unallowedLists = $lists['unallowed_lists'];

        $this->verifyComplianceToAccessLevel(
            $accessLevel, $allowedConnections, $unallowedConnections, $allowedLists, $unallowedLists
        );
        $this->setLists($allowedConnections, $unallowedConnections, $allowedLists, $unallowedLists);
    }

    public function accessLevel(): int {
        return $this->accessLevel;
    }
    
    public function isRestrictedWhenProfileClosed(): bool {
        return true;
    }

//    function setAllowedConnections(array $connections) {
//        $albumOwner = $this->album->user();
//        $this->allowedConnections = new ArrayCollection();
//        
//        foreach ($connections as $connection) {
//            if($albumOwner->id() !== $connection->user1Id() && $albumOwner->id() !== $connection->user2Id() ) {
//                $message = "Cannot add connection {$connection->id()} to list of allowed connections of '{$this->name}' album privacy setting,
//                            because owner of album is not a participant of connection";
//                throw new DomainException($message);
//            }
//            $this->allowedConnections->add($connection);
//        }
//    }
//    
//    function setUnallowedConnections(array $connections) {
//        $albumOwner = $this->album->user();
//        $this->unallowedConnections = new ArrayCollection();
//        
//        foreach ($connections as $connection) {
//            if($albumOwner->id() !== $connection->user1Id() && $albumOwner->id() !== $connection->user2Id() ) {
//                $message = "Cannot add connection {$connection->id()} to list of unallowed connections of '{$this->name}' album privacy setting,
//                            because owner of album is not a participant of connection";
//                throw new DomainException($message);
//            }
//        }
//    }
//    
//    function setAllowedConnectionsLists(array $connectionsLists) {
//        $albumOwner = $this->album->user();
//        $this->allowedLists = new ArrayCollection();
//        
//        foreach ($connectionsLists as $list) {
//            if(!$albumOwner->equals($list->user())) {
//                $message = "Cannot add 'connections list' {$list->id()} to list of 'allowed connections lists' of '{$this->name}' album privacy setting,
//                            because owner of album is not an owner of 'connections list'";
//                throw new DomainException($message);
//            }
//            $this->allowedLists->add($list);
//        }
//    }
//    
//    function setUnallowedConnectionsLists(array $connectionsLists) {
//        $albumOwner = $this->album->user();
//        $this->unallowedLists = new ArrayCollection();
//        
//        foreach ($connectionsLists as $list) {
//            if(!$albumOwner->equals($list->user())) {
//                $message = "Cannot add 'connections list' {$list->id()} to list of 'unallowed connections lists' of '{$this->name}' album privacy setting,
//                            because owner of album is not an owner of 'connections list'";
//                throw new DomainException($message);
//            }
//            $this->unallowedLists->add($list);
//        }
//    }

}
