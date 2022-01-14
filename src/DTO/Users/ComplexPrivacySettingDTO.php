<?php
declare(strict_types=1);
namespace App\DTO\Users;

use App\Domain\Model\Users\Connection\Connection;
use App\Domain\Model\Users\ConnectionsList\ConnectionsList;
use App\DTO\Users\ConnectionDTO;
use App\DTO\Users\ConnectionsListDTO;

class ComplexPrivacySettingDTO implements \App\DTO\Common\DTO {
    
    public int $access_level;
    /** @var array<int,ConnectionDTO> $allowed_connections */
    public array $allowed_connections;
    /** @var array<int,ConnectionDTO> $unallowed_connections */
    public array $unallowed_connections;
    /** @var array<int,ConnectionsListDTO> $allowed_lists */
    public array $allowed_lists;
    /** @var array<int,ConnectionsListDTO> $unallowed_lists */
    public array $unallowed_lists;

    /**
     * @param array<int,ConnectionDTO> $allowedConnections
     * @param array<int,ConnectionDTO> $unallowedConnections
     * @param array<int,ConnectionsListDTO> $allowedLists
     * @param array<int,ConnectionsListDTO> $unallowedLists
     */
    function __construct(
        int $accessLevel,
        array $allowedConnections,
        array $unallowedConnections,
        array $allowedLists,
        array $unallowedLists
    ) {
        $this->access_level = $accessLevel;
        $this->allowed_connections = $allowedConnections;
        $this->unallowed_connections = $unallowedConnections;
        $this->allowed_lists = $allowedLists;
        $this->unallowed_lists = $unallowedLists;
    }

}
