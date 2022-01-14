<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Post\Post;
use App\Domain\Model\Users\Post\Comments\Comment as PostComment;
use App\Domain\Model\Users\Post\Comment\Reply\Reply;
use App\Domain\Model\Pages\Page\Page;

class UserCommentsAuth {
    use AuthorizationTrait;
    
    function failIfCannotSee(User $requester, ProfileComment $comment) {
        if(!$requester->equals($comment->owner())) {
            $this->failIfUserIsInactive($comment->owner(), "Account of comment owner is ");
            $this->failIfUserIsDeleted($comment->owner(), "Comment not found");
            
            if($comment->isSoftlyDeleted()) {
                throw new ForbiddenException(111, "Access to comment is forbidden, comment in recycle bin");
            }
            if(!$this->privacy->isProfileAccessibleTo($requester, $comment->owner())) {
                $this->throwPrivacyException();
            }
            $this->failIfInBlacklist($requester, $comment->owner(), "");
        }
        $commentId = $comment->id();
        
        if($comment instanceof PostComment) {
            if(!$requester->equals($comment->owner()) && $comment->commentedPost()->isSoftlyDeleted()) {
                throw new ForbiddenException(111, "Access to comment is forbidden, comment in recycle bin");
            }
            if(!$this->privacy->canSeePost($requester, $comment->commentedPost())) {
                throw new ForbiddenException(111, "Access to comment is forbidden by privacy");
            }
        }
        elseif($comment instanceof PhotoComment) {
            if(!$requester->equals($comment->owner()) && $comment->photo()->isSoftlyDeleted()) {
                throw new ForbiddenException(111, "Access to comment is forbidden, comment in recycle bin");
            }
            if(!$this->privacy->canSeeAlbum($requester, $comment->photo()->album())) {
                throw new ForbiddenException(111, "Access to comment is forbidden by privacy");
            }
        }
        elseif($comment instanceof VideoComment) {
            if(!$requester->equals($comment->owner()) && $comment->video()->isSoftlyDeleted()) {
                throw new ForbiddenException(111, "Access to comment is forbidden, comment in recycle bin");
            }
            if(!$this->privacy->canSeeVideo($requester, $comment->video())) {
                throw new ForbiddenException(111, "Access to comment is forbidden by privacy");
            }
        }
    }
    
    function failIfCannotUpdatePostComment(User $requester, PostComment $comment): void {
        $this->failIfNoRights($requester, $comment->creator, "Cannot update another's user comment");
    }
    
    function failIfCannotUpdatePostCommentReply(User $requester, Reply $reply): void {
        //$this->failIfAccessToPostProhibited($requester, $reply->post());   
        $this->failIfNoRights($requester, $reply->user, "Cannot update another's user reply");       
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
    }
    
    function failIfCannotCommentAsPage(User $requester, Page $page): void {

    }

}
