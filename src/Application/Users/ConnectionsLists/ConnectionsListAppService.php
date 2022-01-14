<?php
declare(strict_types=1);
namespace App\Application\Users\ConnectionsLists;

use App\Application\Exceptions\NotExistException;
use App\Application\ApplicationService;
use App\Domain\Model\Users\ConnectionsList\ConnectionsList;
use App\Domain\Model\Users\ConnectionsList\ConnectionsListRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Connection\ConnectionRepository;

abstract class ConnectionsListAppService implements ApplicationService {
    use \App\Application\AppServiceTrait;
    
    protected ConnectionsListRepository $lists;
    protected UserRepository $users;
    protected ConnectionRepository $connections;
            
    function __construct(ConnectionsListRepository $lists, UserRepository $users, ConnectionRepository $connections) {
        $this->lists = $lists;
        $this->users = $users;
        $this->connections = $connections;
    }

    protected function findListOrFail(string $listId): ConnectionsList {
        $list = $this->lists->getById($listId);
        
        if(!$list || $list->user()->isDeleted()) {
            throw new NotExistException("Connections list $listId not found");
        }
        return $list;
    }
    
}