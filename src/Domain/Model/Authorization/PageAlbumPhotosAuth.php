<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Application\Exceptions\ForbiddenException;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Post\Post;
use App\Domain\Model\Pages\Photos\AlbumPhoto\AlbumPhoto;
use App\Domain\Model\Pages\Photos\AlbumPhoto\Comment\Comment;
use App\Application\Errors;

class PageAlbumPhotosAuth {

    function canSee(User $requester, AlbumPhoto $photo): bool {

    }
    
    function failIfCannotSee(User $requester, AlbumPhoto $photo): void {
        $group = $photo->owningGroup();
        $requesterIsGlobalManager = $requester->isGlobalManager();

        if($this->isGroupInactive($group) && !$requesterIsGlobalManager) {
            throw new ForbiddenException(123, "No rights");
        }
        if($group->isManager($requester)) {
            return;
        }

        if($group->isPrivate() && !$group->isMember($requester) && !$group->isManager($requester) && !$requesterIsGlobalManager) {
            throw new ForbiddenException(111, "No rights");
        }

    }
    
    function failIfCannotComment(User $requester, AlbumPhoto $photo): void {
        $group = $photo->owningGroup();

        if($this->isGroupInactive($group)) {
            throw new ForbiddenException(123, "Group is inactive");
        }

        if($this->failIfBanned($requester)) {
            throw new ForbiddenException(\App\Application\Errors::BANNED_IN_GROUP, "Banned users cannot comment album photos");
        }
        
        if(!$group->isMember($requester) && !$group->isManager($requester)) {
            throw new ForbiddenException(111, "No rights");
        }
    }

    function failIfCannotEditComment(User $requester, Comment $comment): void {
        $group = $comment->owningGroup();

        if($this->isGroupInactive($group)) {
            throw new ForbiddenException(123, "Group is inactive");
        }
        
        
        if($this->failIfBanned($requester)) {
            throw new ForbiddenException(Errors::BANNED_IN_GROUP, "Banned users cannot edit comments to album photos");
        }
        
        if(!$group->isMember($requester) && !$group->isManager($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "No rights");
        }
        if(!$comment->creator()->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "No rights");
        }
    }
    
    function failIfCannotDeleteComment(User $requester, Comment $comment): void {

        if(!$comment->creator()->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "No rights");
        }
    }
    
    function failIfCannotReact(User $requester, AlbumPhoto $photo): void {
        $group = $photo->owningGroup();

        if($this->isGroupInactive($group)) {
            throw new ForbiddenException(123, "Group is inactive");
        }
        if($this->isPermanentlyBanned($requester)) {
            throw new ForbiddenException(123, "Permanently banned users cannot react");
        }
        if($group->isPrivate() && !$group->isMember($requester) && !$group->isManager($requester)) {
            throw new ForbiddenException(111, "No rights");
        }
    }
    
    function failIfCannotDeleteReaction(User $requester, Reaction $reaction): void {

        if(!$reaction->creator()->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "No rights");
        }
    }
    
}