<?php
declare(strict_types=1);
namespace App\Application\Users;

use App\Application\Exceptions\NotExistException;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\RequestParamsValidator;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Groups\Group\GroupRepository;
use App\Application\ApplicationService;
use App\Domain\Model\Groups\Group\Group;

abstract class GroupsApplicationService implements ApplicationService {
    use \App\Application\AppServiceTrait;
    
    protected GroupRepository $groups;
            
    function __construct(
        UserRepository $users,
        RequestParamsValidator $validator,
        GroupRepository $groups
    ) {
        $this->users = $users;
        $this->validator = $validator;
        $this->groups = $groups;
    }
    
    protected function findGroupOrFail($groupId, bool $asTarget): Group {
        $group = $this->findGroup($groupId);
        if(!$group && $asTarget) {
            throw new NotExistException('Группа не найдена');
        } elseif(!$group && !$asTarget) {
            throw new UnprocessableRequestException('Группа не найдена');
        }
        return $group;
    }
    
    protected function findGroup($groupId): ?Group {
        return $this->groups->getById($groupId);
    }
}