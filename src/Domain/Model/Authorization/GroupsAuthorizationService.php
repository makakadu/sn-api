<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Domain\Model\Users\PrivacyService\PrivacyService;
use App\Domain\Model\Users\User\User;
use App\Application\Errors;
use App\Application\Exceptions\ForbiddenException;
use App\Domain\Model\Groups\Group\Group;
use App\Domain\Model\Groups\Post\Post;

class GroupsAuthorizationService {
    
    protected PrivacyService $privacy;
    
    function __construct(PrivacyService $privacy) {
        $this->privacy = $privacy;
    }
    
    function failIfCannotInviteToGroup(User $initiator, User $target, Group $group): void {
        if(!$this->privacy->canInviteToGroup($initiator, $target)) {
            throw new ForbiddenException(228, "Нельзя из-за настроек приватности");
        } elseif($group->isClosed() && !$group->isAdmin($initiator)) {
            throw new ForbiddenException(229, "Если группа закрыта, то только админы могут приглашать");
        }
        $this->failIfBannedInGroup($group, $initiator, "Забаненный не может приглашать в группу");
        $this->failIfBannedInGroup($group, $target, "Нельзя пригласить забаненного в группу");
    }
    
    function failIfCannotCreatePost(User $user, Group $group): void {
        $this->failIfBannedInGroup($group, $user);
        $this->failIfWallIsClosed($group, null);
        $this->failIfClosedForUser($user, $group);    
        
        $wallSection = $group->getSectionSettings()->wall();
        if(($wallSection === 2 || $wallSection === 3) && !$group->isAdminOrEditor($user)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, 'Cannot create post on another profile');
        }
    }
    
    function failIfCannotEditPost(User $user, Post $post): void {
        $group = $post->group;
        $this->failIfBannedInGroup($group, $user);
        $this->failIfWallIsClosed($post->group, null);
        
        if($post->onBehalfOfGroup()) {
            $this->failIfNotAdminOrEditor($user, $group);
        } else {
            $this->failIfClosedForUser($user, $group);
            if(!$post->creator()->equals($user)) {
                throw new ForbiddenException(Errors::NO_RIGHTS, 'No rights');
            }
        }
    }
    
    function failIfCannotRemovePost(User $user, Post $post): void {
        $group = $post->group;
        $this->failIfBannedInGroup($group, $user);
        $this->failIfWallIsClosed($post->group, null);
        
        if($post->onBehalfOfGroup()) {
            $this->failIfNotAdminOrEditor($user, $group);
        } else {
            $this->failIfClosedForUser($user, $group);
            if(!$post->creator()->equals($user) && !$group->isAdminOrEditor($user)) {
                throw new ForbiddenException(Errors::NO_RIGHTS, 'No rights');
            }
        }
    }

    private function failIfNotAdminOrEditor(User $user, Group $group) {
        if($group->isAdminOrEditor($user)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, 'No rights');
        }
    }
    
    private function failIfClosedForUser(User $user, Group $group) {
        if($group->isClosed() && !$group->isMember($user)) {
            throw new ForbiddenException(222, 'Group is closed');
        }
    }
    
    private function failIfWallIsClosed(Group $group, ?string $message): void {
        $wallSection = $group->getSectionSettings()->wall();
        if($wallSection === 0) {
            throw new ForbiddenException(222, $message ?? 'Wall section is closed');
        } 
    }

    protected function failIfBannedInGroup(Group $group, User $user): void {
        if(\in_array($user->id(), $group->blacklist())) {
            throw new ForbiddenException(2222, "Banned in group");
        }
    }
    
    protected function failIfGroupIsInactive(Group $group): void {
        if($this->isGroupInactive($group)) {
            $inactivityReason = $this->getReasonOfGroupInactivity($group);
            throw new ForbiddenException(111, "Access to resource forbidden, group was $inactivityReason");
        }
    }
    
    function isGroupInactive(Group $group): bool {
        return false;
    }
    
    function getReasonOfGroupInactivity(Group $group): ?string {
        return null;
    }
}
