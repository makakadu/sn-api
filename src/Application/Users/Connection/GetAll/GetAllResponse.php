<?php
declare(strict_types=1);
namespace App\Application\Users\Connection\GetAll;

use App\Domain\Model\Users\Connection\Connection;

class GetAllResponse implements \App\Application\BaseResponse {

    /** @var array<mixed> $items */
    public array $items;

    /**
     * @param array<Connection> $connections
     */
    public function __construct(array $connections) {
        foreach($connections as $connection) {
            echo $connection->id();
        }
    }
}
