<?php
declare(strict_types=1);
namespace App\Application\Users;

use App\Application\Exceptions\NotExistException;
use App\Application\ApplicationService;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use App\Domain\Model\Authorization\ConnectionsAuth;
use App\Domain\Model\Users\Connection\Connection;

abstract class ConnectionAppService implements ApplicationService {
    use \App\Application\AppServiceTrait;
    
    protected ConnectionRepository $connections;
    protected ConnectionsAuth $auth;
    
    function __construct(UserRepository $users, ConnectionRepository $connections, ConnectionsAuth $auth) {
        $this->users = $users;
        $this->connections = $connections;
        $this->auth = $auth;
    }
    
    protected function findConnectionOrFail(string $connectionId): Connection {
        $connection = $this->connections->getById($connectionId);
        if(!$connection) {
            throw new NotExistException("Connection $connectionId not found");
        }
        return $connection;
    }

}