<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;

use App\Domain\Model\Users\Connection\Connection;
use App\DTO\Users\ConnectionDTO;

class ConnectionTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transform(Connection $connection): ConnectionDTO {
        
        return new ConnectionDTO(
            $connection->id(),
            $this->userToSmallDTO($connection->getUser1()),
            $this->userToSmallDTO($connection->getUser2()),
            $connection->isAccepted()
        );
    }
    
    function transformMultiple(array $connections): array {
        $conns = [];
        foreach($connections as $conn) {
            $conns[] = $this->transform($conn);
        }
        return $conns;
    }
    
}