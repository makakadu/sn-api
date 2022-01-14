<?php
declare(strict_types=1);
namespace App\Application\Groups;

use App\Application\Exceptions\NotExistException;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Domain\Model\Groups\Group\GroupRepository;
use App\Domain\Model\Groups\Group\Group;

trait GroupAppServiceTrait {
    use \App\Application\AppServiceTrait;
    
    private GroupRepository $groups;
            
    function __construct(GroupRepository $groups) {
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