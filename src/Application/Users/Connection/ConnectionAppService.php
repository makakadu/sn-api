<?php
declare(strict_types=1);
namespace App\Application\Users\Connection;

use App\Domain\Model\Users\Connection\ConnectionRepository;
use App\Domain\Model\Users\Connection\Connection;
use App\Application\Exceptions\NotExistException;
use App\Application\Exceptions\UnprocessableRequestException;

trait ConnectionAppService {
    
    protected ConnectionRepository $connections;

    function findConnectionOrFail(string $connectionId, bool $asTarget): ?Connection {
        $connection = $this->connections->getById($connectionId);
        
        $found = true;
        if(!$connection) {
            $found = false;
        }
        if(!$found && $asTarget) {
            throw new NotExistException("Connection $connectionId not found");
        } elseif(!$found && !$asTarget) {
            throw new UnprocessableRequestException("Connection $connectionId not found");
        }
        return $connection;
    }

}