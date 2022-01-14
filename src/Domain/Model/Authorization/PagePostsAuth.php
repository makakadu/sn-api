<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Post\Post;
use App\Domain\Model\Pages\Post\Comment\Comment;
use App\Domain\Model\Common\Post as AbstractPost;
use App\Domain\Model\Groups\Membership\Membership;

class PagePostsAuth {
    use AuthorizationTrait;
            
    function canSee(User $requester, Post $post): bool {
        return true;
    }
    
    function failIfCannotComment(User $requester, Post $post): void {
        $postGroup = $post->group();
        
        $this->failIfBannedInGroup($requester, $postGroup, 'Banned in group');
        $this->failIfClosedForUser($requester, $postGroup);
        $this->failIfCannotCommentPosts($requester, $postGroup, "Commenting is prohibited because wall is closed");
    }
    
    function failIfCannotCommentPosts(User $requester, Group $group, string $message) {

        if($group->wallIsClosed()) {
            $membership = $this->memberships->getByUserAndGroup($requester, $group);
            $isAdminOrEditor = $membership && ($membership->status() === Membership::ADMIN || $membership->status() === Membership::EDITOR);
            
            if(!$isAdminOrEditor) {
                throw new ForbiddenException(333, $message);
            }
        }
    }
    
    function failIfCannotUpdatePostComment(User $requester, Comment $comment): void {
        $group = $comment->group();
        $post = $comment->commentedPost();
        
        if($post->commentsAreDisabled()) {
            throw new ForbiddenException(111, "Comments are disabled");
        }
        $this->failIfBannedInGroup($requester, $group, 'Banned in group');
        $this->failIfClosedForUser($requester, $group);
        
        $this->failIfCannotCommentPosts($requester, $group, "Comments editing is prohibited because wall is closed");
        
        $commentCreator = $comment->creator();
        $onBehalfOfCommunity = $comment->onBehalfOfCommunity();
        
        if($onBehalfOfCommunity) {
            $community = $comment->community();
            if($community instanceof Group) {
                $membership = $this->memberships->getByUserAndGroup($requester, $community);
                if($membership->status() !== Membership::ADMIN && $membership->status() !== Membership::EDITOR) {
                    throw new ForbiddenException(444, "Cannot change comment of group");
                }
            } elseif ($community instanceof _Public) {
                $subscription = $this->subscription->getByUserAndPublic($requester, $community);
                if($subscription->status() !== Subscription::ADMIN && $subscription->status() !== Subscription::EDITOR) {
                    throw new ForbiddenException(444, "Cannot change comment of public");
                }
            }
        } else {
            if(!$commentCreator->equals($requester)) {
                throw new ForbiddenException(333, "Cannot change comment of another user");
            }
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
    
    function failIfCannotUpdatePost(User $requester, Post $post) {
        //$this->failIfResourceOwnerIsInactive($postCreator);
        if($requester->equals($post->creator())) { return; }
    }
    
    function failIfCannotRemovePost(User $requester, Post $post) {
        $postCreator = $post->creator();
        $this->failIfUserIsInactive($postCreator);
        
        if($requester->equals($postCreator)) {
            return;
        } elseif($requester->isModer() && !$postCreator->isModer() && !$postCreator->isAdmin()) {
            return;
        } elseif($requester->isAdmin() && !$postCreator->isAdmin()) {
            return;
        }
    }
    
    function failIfCannotRemoveComment(User $requester, Comment $comment) {
        $postCreator = $comment->commentedPost()->creator();
        $this->failIfUserIsInactive($postCreator);
//        
//        if($requester->equals($postCreator)) {
//            return;
//        } elseif($requester->isModer() && !$postCreator->isModer() && !$postCreator->isAdmin()) {
//            return;
//        } elseif($requester->isAdmin() && !$postCreator->isAdmin()) {
//            return;
//        }
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
