<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;

use App\Domain\Model\Users\Connection\Connection;
use App\Domain\Model\Users\ConnectionsList\ConnectionsList;
use App\DTO\Users\ComplexPrivacySettingDTO;
use App\DTO\Users\ConnectionDTO;
use App\DTO\Users\ConnectionsListDTO;

class ComplexPrivacySettingTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transform(\App\Domain\Model\Users\ComplexPrivacySetting $setting): ComplexPrivacySettingDTO {

        /** @var array<int,ConnectionDTO> $allowedConnections */
        $allowedConnections = [];
        foreach ($setting->allowedConnections() as $allowedConnection) {
            $allowedConnections[] = new \App\DTO\Users\ConnectionDTO(
                $allowedConnection->user1Id(),
                $allowedConnection->user2Id(),
                $allowedConnection->isAccepted()
            );
        }
        /** @var array<int,ConnectionDTO> $unallowedConnections */
        $unallowedConnections = [];
        /** @var array<int,ConnectionsListDTO> $allowedLists */
        $allowedLists = [];
        /** @var array<int,ConnectionsListDTO> $unallowedLists */
        $unallowedLists = [];
        
        return new ComplexPrivacySettingDTO(
            $setting->accessLevel(),
            $allowedConnections,
            $unallowedConnections,
            $allowedLists,
            $unallowedLists,
        );
    }
}
