<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Post\Post;
use App\Domain\Model\Users\Post\Comments\Comment;
use App\Application\Exceptions\ForbiddenException;
use App\Domain\Model\Users\Post\Reaction;
use App\Domain\Model\Users\User\ProfilePrivacySettings as PS;
use App\Application\Errors;
use App\Domain\Model\Users\Post\Comments\Reaction as CommentReaction;
use App\Domain\Model\Users\Connection\ConnectionRepository;

class UserPostsAuth {
    use AuthorizationTrait;
    
    private ConnectionRepository $connections;
    
    function __construct(\App\Domain\Model\Users\PrivacyService\PrivacyResolver_new $privacy, ConnectionRepository $connections) {
        $this->privacy = $privacy;
        $this->connections = $connections;
    }
    
    function failIfCannotSave(User $requester, Post $post) {
        $this->failIfUserIsInactive($post->creator(), "Cannot save post, post is unnaccessible because owner is %");
        //$this->failIfUserIsDeleted($post->creator(), "Post owner is deleted");
        $this->failIfInTrash($post, "Cannot save post, post is unnaccessible because in trash");
        $this->failIfUnaccessibleToUserByPrivacy($requester, $post, "Cannot save post, post is unnaccessible because prohibited by privacy settings");
    }
    
    function failIfCannotSee(User $requester, Post $post): void {
        $this->failIfUserIsInactive($post->creator(), "Access to post is prohibited, post owner is %");
        //$this->failIfUserIsDeleted($post->creator(), "Post owner is deleted");
        $this->failIfInTrash($post, "Access to post is prohibited, post in trash");
        $this->failIfUnaccessibleToUserByPrivacy($requester, $post, "Access to post is prohibited by post privacy settings");
    }
    
    function failIfGuestsCannotSee(Post $post): void {
        $this->failIfUserIsInactive($post->creator(), "Access to post is prohibited, post owner is %");
        $this->failIfInTrash($post, "Access to post is prohibited, post in trash");
        if(!$post->isPublic()) {
            throw new ForbiddenException(111, "Access to post is prohibited by post privacy settings");
        }
    }
    
    function canSee(User $requester, Post $post): bool {
        try {
            $this->failIfCannotSee($requester, $post);
        } catch (\Exception $ex) {
            return false;
        }
        return true;
    }
    
    function guestsCanSee(Post $post): bool {
        try {
            $this->failIfGuestsCannotSee($post);
        } catch (\Exception $ex) {
            return false;
        }
        return true;
    }

    function failIfCannotEdit(User $requester, Post $post): void {

        if(!$post->creator()->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot edit post from another profile");
        }
    }
    
    function failIfCannotDelete(User $requester, Post $post, bool $asGlobalManager): void {
        $postCreator = $post->creator();
        $this->failIfUserIsInactive($postCreator, "Profile owner is ");
        // $this->failIfUserIsDeleted($postCreator, "Profile owner is deleted");
        
        if($asGlobalManager && !$requester->isGlobalManager()) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "No rights to softly delete as manager");
        } elseif(!$asGlobalManager && !$postCreator->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "No rights");
        }
    }

    function failIfCannotComment(User $requester, Post $post): void {
        $postOwner = $post->creator();

        $this->failIfUserIsInactive($postOwner, "Cannot comment post, post is unaccessible because owner is %");
        
        if(!$postOwner->equals($requester)) {
            $this->failIfInBlacklist($requester, $postOwner, "Post commenting is forbidden to banned users");
            $this->failIfUnaccessibleToUserByPrivacy($requester, $post, "Cannot comment post, access to post is prohibited by post privacy settings");
            
            $whoCanCommentPrivacySetting = $postOwner->getPrivacySetting(PS::COMMENT_POSTS);
            if(!$this->privacy->hasAccess($requester, $whoCanCommentPrivacySetting)) {
                throw new ForbiddenException(Errors::PROHIBITED_BY_PRIVACY, "Post commenting is forbidden by profile privacy settings");
            }
        }
    }

    function failIfCannotEditComment(User $requester, Comment $comment): void {
        $post = $comment->commentedPost();
        $postOwner = $post->owner();
        
        $this->failIfUserIsInactive($postOwner, "Access is prohibited, post owner is %");
        
        if(!$postOwner->equals($requester)) {
            $this->failIfInBlacklist($requester, $postOwner, "Editing comments is prohibited to banned users");
            $this->failIfUnaccessibleToUserByPrivacy($requester, $post, "Editing comments is prohibited by profile privacy settings");
        }
        
        if(!$comment->creator()->equals($requester)) { // Только создатель может отредачить свой коммент
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot edit comment created by another user");
        }
    }
    
    function failIfCannotDeleteComment(User $requester, Comment $comment): void {
        $post = $comment->commentedPost();
        $postOwner = $$post->owner();

        if(!$comment->creator()->equals($requester) && !$postOwner->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot delete comment created by another user");
        }
    }

    function failIfCannotReact(User $requester, Post $post, ?\App\Domain\Model\Pages\Page\Page $asPage): void {
        $postOwner = $post->creator();
        $this->failIfUserIsInactive($postOwner, "Cannot react to post, post is unaccessible because owner is %");
        if($asPage && !$asPage->isAdminOrEditor($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot react to post on behalf of page {$asPage->id()}");
        }
        $this->failIfInBlacklist($requester, $postOwner, "Cannot react to post created on profile where requester is banned");
        $this->failIfUnaccessibleToUserByPrivacy($requester, $post, "Cannot react to post, access to post is prohibited by post privacy settings");
    }
    
    function failIfCannotEditReaction(User $requester, Reaction $reaction): void {
        $post = $reaction->post();
        $postOwner = $post->creator();
        
        $this->failIfUserIsInactive($postOwner, "Cannot edit reaction to post, post is unaccessible because owner is %");
        $this->failIfUnaccessibleToUserByPrivacy($requester, $post, "Cannot edit reaction to post, access to post is prohibited by post privacy settings");
        $this->failIfInBlacklist($requester, $post->creator(), "Cannot edit reaction to post created on profile where requester is banned");
        if(!$reaction->creator()->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot edit reaction to post created by another user");
        }
    }
    
    function failIfCannotDeleteReaction(User $requester, Reaction $reaction): void {
        if(!$reaction->creator()->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot delete reaction created by another user");
        }
    }
    
    function failIfCannotReactToComment(User $requester, Comment $comment): void {
        $post = $comment->commentedPost();
        $postOwner = $post->owner();
        
        $this->failIfUserIsInactive($postOwner, "Cannot react to comment from post, post is unaccessible because owner is %");
        $this->failIfInBlacklist($requester, $post->owner(), "Cannot react to comment from post created on profile where requester is banned");
        $this->failIfUnaccessibleToUserByPrivacy($requester, $post, "Cannot react to comment from post, access to post is prohibited by post privacy settings");
    }
    
    function failIfCannotEditCommentReaction(User $requester, Reaction $reaction): void {
        $post = $reaction->post();
        $postOwner = $post->owner();
        
        $this->failIfUserIsInactive($postOwner, "Cannot edit reaction to comment from post, post is unaccessible because owner is %");
        $this->failIfInBlacklist($requester, $post->owner(), "Cannot edit reaction to comment from post created on profile where requester is banned");
        $this->failIfUnaccessibleToUserByPrivacy($requester, $post, "Cannot edit reaction to comment from post, access to post is prohibited by post privacy settings");
        
        if(!$reaction->creator()->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot edit reaction created by another user");
        }
    }
    
    function failIfCannotDeleteCommentReaction(User $requester, CommentReaction $reaction): void {
        if(!$reaction->creator()->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot delete reaction created by another user");
        }
    }
    
    function failIfUnaccessibleToUserByPrivacy(User $requester, Post $post, string $failMessage): void {
        $postOwner = $post->creator();
        
        if($postOwner->equals($requester)) {
            return;
        }
        $connection = $this->connections->getByUsersIds($requester->id(), $postOwner->id());
        $areFriends = $connection && $connection->isAccepted();
        
        if(!$post->isPublic() && !$postOwner->equals($requester) && !$areFriends) {
            throw new ForbiddenException(Errors::PROHIBITED_BY_PRIVACY, $failMessage);
        }
        
    }
    
    function failIfInTrash(Post $post, string $message) {
        if($post->isSoftlyDeleted()) {
            throw new ForbiddenException(Errors::SOFTLY_DELETED, $message);
        }
    }
}
