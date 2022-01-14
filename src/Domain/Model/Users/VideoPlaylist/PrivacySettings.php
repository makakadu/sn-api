<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\VideoPlaylist;

use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\ComplexPrivacySettingTrait;
use App\Domain\Model\Users\ComplexPrivacySetting;
use App\Domain\Model\Users\Connection\Connection;
use App\Domain\Model\Users\ConnectionsList\ConnectionsList;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\DomainException;

class PrivacySettings implements ComplexPrivacySetting {
    use EntityTrait;
    use ComplexPrivacySettingTrait;
    
    private string $playlistId;
    
    private VideoPlaylist $videoPlaylist;
    
    /**
     * @param array<mixed> $lists
     */
    function __construct(VideoPlaylist $playlist, int $accessLevel, array $lists) {
        $this->failIfOutOfRange($accessLevel);
        $this->accessLevel = $accessLevel;
        $this->videoPlaylist = $playlist;
        $this->createdAt = new \DateTime("now");
        
        $allowedConnections = $lists['allowed_connections'];
        $unallowedConnections = $lists['unallowed_connections'];
        $allowedLists = $lists['allowed_lists'];
        $unallowedLists = $lists['unallowed_lists'];
        
        $user = $playlist->user();
        
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
                throw new DomainException("Incorrect privacy data, playlist creator ({$user->id()}) is not participant of connection {$conn->id()}");
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
                throw new DomainException("Incorrect privacy data, playlist creator ({$user->id()}) is not owner of connections list {$list->id()}");
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

}
