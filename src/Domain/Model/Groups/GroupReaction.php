<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Group\Group;

abstract class GroupReaction extends \App\Domain\Model\Common\Reaction {
    use \App\Domain\Model\Groups\GroupEntity;
    use \App\Domain\Model\EntityTrait;
    
    protected bool $onBehalfOfGroup;

    function __construct(User $creator, Group $owningGroup, string $type, bool $onBehalfOfGroup) {
        $this->id = (string)\Ulid\Ulid::generate(true);
        $this->owningGroup = $owningGroup;
        $this->creator = $creator;
        $this->onBehalfOfGroup = $onBehalfOfGroup;
        $this->reactionType = $type;
        $this->createdAt = new \DateTime('now');
    }
    
    function onBehalfOfGroup(): bool {
        return $this->onBehalfOfGroup;
    }
}
