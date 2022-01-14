<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Videos;

use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\ComplexPrivacySetting;
use App\Domain\Model\Users\ComplexPrivacySettingTrait;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Connection\Connection;
use App\Domain\Model\Users\ConnectionsList\ConnectionsList;
use App\Domain\Model\DomainException;

class PrivacySetting implements ComplexPrivacySetting {
    use EntityTrait;
    use ComplexPrivacySettingTrait;
    
    //private Video $video;
    private string $videoId;
    private string $name;
            
    /**
     * @param array<mixed> $lists
     */
    function __construct(Video $video, string $name, int $accessLevel, array $lists) {
        $this->failIfOutOfRange($accessLevel);
        $this->accessLevel = $accessLevel;
        $this->videoId = $video->id();
        $this->name = $name;
        $this->createdAt = new \DateTime("now");
        $this->ownerId = $video->creator()->id();
        
        $allowedConnections = $lists['allowed_connections'];
        $unallowedConnections = $lists['unallowed_connections'];
        $allowedLists = $lists['allowed_lists'];
        $unallowedLists = $lists['unallowed_lists'];
        
        $this->failIfSameConnectionInBothLists($allowedConnections, $unallowedConnections);
        $this->failIfSameConnectionsListInBothLists($allowedLists, $unallowedLists);
        
        $this->ownerId = $video->creator()->id();
        $creator = $video->creator();
        
        $this->failIfUserIsNotParticipantOfConnection($creator, $allowedConnections);
        $this->failIfUserIsNotParticipantOfConnection($creator, $unallowedConnections);
        $this->failIfUserIsNotOwnerOfConnectionsList($creator, $allowedLists);
        $this->failIfUserIsNotOwnerOfConnectionsList($creator, $unallowedLists);
        
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
                throw new DomainException("Incorrect privacy data, video creator ({$user->id()}) is not participant of connection {$conn->id()}");
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
                throw new DomainException("Incorrect privacy data, video creator ({$user->id()}) is not owner of connections list {$list->id()}");
            }
        }
    }
    
    /**
     * @param array<mixed> $privacyData
     */
    function edit(array $privacyData): void {
        $accessLevel = $privacyData['access_level'];
        $this->failIfOutOfRange($accessLevel);
        $this->accessLevel = $accessLevel;
        
        $this->setLists(
            $privacyData['allowed_connections'] ?? [],
            $privacyData['unallowed_connections'] ?? [],
            $privacyData['allowed_lists'] ?? [],
            $privacyData['unallowed_lists'] ?? []
        );
    }

}
