<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Domain\Model\Groups\Group\Group;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Post\Post;
use App\Domain\Model\Groups\Post\Comment\Comment;
use App\Domain\Model\Common\Post as AbstractPost;
use App\Domain\Model\Groups\Membership\Membership;
use App\Application\Errors;

class GroupPostsAuth {
    use AuthorizationTrait;
            
    function canSee(User $requester, Post $post): bool {
        $postGroup = $post->owningGroup();
        if($postGroup->isClosed() && !$postGroup->isMemberOrManager($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Access is forbidden"); 
        }
    }
            
    function guestsCanSee(Post $post): bool {
        $postGroup = $post->owningGroup();
        if($postGroup->isClosed()) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Access is forbidden"); 
        }
    }
    
    function failIfBannedInGroup(User $requester, Groups $group, string $message) {
        if($this->bans->getByUserAndGroup($requester, $group)) {
            throw new ForbiddenException(666, $message);
        }
    }
    
    function failIfClosedForUser(User $requester, Groups $group) {
        if($group->isClosed() && !$this->memberships->getByUserAndGroup($requester, $group)) {
            throw new ForbiddenException(228, "Group is closed for user");
        }
    }
    
    function failIfCannotUpdatePostCommentReply(User $requester, Reply $reply): void {
        $this->failIfAccessToPostProhibited($requester, $reply->commentedPost());   
        $this->failIfNoRights($requester, $reply->user, "Cannot update another's user reply");       
    }
        
    function failIfAccessToPostProhibited(?User $requester, Post $post) {
        $postCreator = $post->creator();
        $this->failIfUserIsInactive($postCreator);
        
        if($requester) {
            if($requester->equals($postCreator)) { return; }
            $this->failIfBannedByResourceOwner($requester, $postCreator);
            
            if(!$this->privacy->isProfileAccessibleTo($requester, $postCreator)) {
                $this->throwPrivacyException();
            }
        } else {
            if(!$this->privacy->isProfileAccessibleToGuests($postCreator)) {
                $this->throwPrivacyException();
            }
        }
    }
}
