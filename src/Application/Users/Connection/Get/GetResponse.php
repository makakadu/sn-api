<?php
declare(strict_types=1);
namespace App\Application\Users\Connection\Get;

use App\DTO\Users\ConnectionDTO;

class GetResponse implements \App\Application\BaseResponse {

    public ConnectionDTO $connection;
    
    public function __construct(ConnectionDTO $connection) {
        $this->connection = $connection;
    }

}
