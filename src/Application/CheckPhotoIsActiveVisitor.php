<?php
declare(strict_types=1);
namespace App\Application;

use App\Domain\Model\Users\Photos\CommentPhoto\CommentPhoto as CommentPhoto;
use App\Domain\Model\Users\Photos\PostPhoto\PostPhoto as PostPhoto;

class CheckPhotoIsActiveVisitor implements \App\Domain\Model\Common\PhotoVisitor {
    
    private CommentsRepository $comments;
    
    function __construct(CommentsRepository $comments) {
        $this->comments = $comments;
    }
    
    function visitUserPhoto(UserPhoto $photo) {
        if($photo->isSoftlyDeleted() || $photo->owner()->isSoftlyDeleted()) {
            return false;
        }
        
        if($photo->commentId()) {
            $comment = $this->comments->getById($comment);
            $commented = $comment->commentedPost();
            // должен называться одинаково
            $commentedOwner = $commented->owner();
            
            if($comment->isSoftlyDeleted() || $commented->isSoftlyDeleted() || $commentedOwner->isSoftlyDeleted()) {
                return false;
            }
        } elseif($photo->commentedPost()) {
            $post = $photo->commentedPost();
            if($post && ($post->isSoftlyDeleted() || $post->creator()->isSoftlyDeleted())) {
                return false;
            }
        } elseif($photo->album()) {
            $album = $photo->album();
            if($album && ($album->isSoftlyDeleted() || $album->creator()->isSoftlyDeleted())) {
                return false;
            }
        }
        
        return true;
    }
    
    function visitGroupPhoto(GroupPhoto $photo) {
        if($photo->isSoftlyDeleted() || $photo->group()->isSoftlyDeleted()) {
            return false;
        }
        
        if($photo->commentId()) {
            $comment = $this->comments->getById($comment);
            $commented = $comment->commentedPost();
            $commentedGroup = $commented->group();
            
            if($comment->isSoftlyDeleted() || $commented->isSoftlyDeleted() || $commentedGroup->isSoftlyDeleted()) {
                return false;
            }
        } elseif($photo->commentedPost()) {
            $post = $photo->commentedPost();
            if($post && ($post->isSoftlyDeleted() || $post->group()->isSoftlyDeleted())) {
                return false;
            }
        } elseif($photo->album()) {
            $album = $photo->album();
            if($album && ($album->isSoftlyDeleted() || $album->group()->isSoftlyDeleted())) {
                return false;
            }
        }
        
        return true;
    }

    function visitUserCommentPhoto(CommentPhoto $photo) {
        $commentId = $photo->commentId();
        
        if($commentId) {
            $comment = $this->comments->getById($commentId);
            $commented = $comment->commentedPost();
            $commentedCreator = $commented->creator();
            if($comment->isSoftlyDeleted() || $commented->isSoftlyDeleted() || $commentedCreator->isSoftlyDeleted()) {
                return false;
            }
        }
        return true;
    }
    
    function visitUserPostPhoto(PostPhoto $photo) {
        $post = $photo->commentedPost();
        if($post && ($post->isSoftlyDeleted() || $post->creator()->isSoftlyDeleted())) {
            return false;
        }
        return true;
    }
    
    function visitUserAlbumPhoto(UserAlbumPhoto $photo) {
        $album = $photo->album();
        if($album && ($album->isSoftlyDeleted() || $album->creator()->isSoftlyDeleted())) {
            return false;
        }
        return true;
    }
    
    function visitUserPicture(UserPicture $picture) {
        if($picture->user()->isSoftlyDeleted()) {
            return false;
        }
        return true;
    }

    public function visitGroupAlbumPhoto(\App\Domain\Model\Common\GroupAlbumPhoto $photo) {
        $album = $photo->album();
        if($album && ($album->isSoftlyDeleted() || $album->group()->isSoftlyDeleted())) {
            return false;
        }
        return true;
    }

    public function visitGroupCommentPhoto(\App\Domain\Model\Common\GroupCommentPhoto $photo) {
        $commentId = $photo->commentId();
        
        if($commentId) {
            $comment = $this->comments->getById($commentId);
            $commented = $comment->commentedPost();
            $commentedGroup = $commented->group();

            if($comment->isSoftlyDeleted() || $commented->isSoftlyDeleted() || $commentedGroup->isSoftlyDeleted()) {
                return false;
            }
        }
        return true;
    }

    public function visitGroupPicture(\App\Domain\Model\Common\GroupPicture $picture) {
        if($picture->group()->isSoftlyDeleted()) {
            return false;
        }
        return true;
    }

    public function visitGroupPostPhoto(\App\Domain\Model\Common\GroupPostPhoto $photo) {
        $post = $photo->commentedPost();
        if($post && ($post->isSoftlyDeleted() || $post->group()->isSoftlyDeleted())) {
            return false;
        }
        return true;
    }

    public function visitPageAlbumPhoto(\App\Domain\Model\Common\PageAlbumPhoto $photo) {
        $album = $photo->album();
        if($album && ($album->isSoftlyDeleted() || $album->asPage()->isSoftlyDeleted())) {
            return false;
        }
        return true;
    }

    public function visitPageCommentPhoto(\App\Domain\Model\Common\PageCommentPhoto $photo) {
        $commentId = $photo->commentId();
        
        if($commentId) {
            $comment = $this->comments->getById($commentId);
            $commented = $comment->commentedPost();
            $commentedPage = $commented->asPage();

            if($comment->isSoftlyDeleted() || $commented->isSoftlyDeleted() || $commentedPage->isSoftlyDeleted()) {
                return false;
            }
        }
        return true;
    }

    public function visitPagePicture(\App\Domain\Model\Common\PagePicture $picture) {
        if($picture->asPage()->isSoftlyDeleted()) {
            return false;
        }
        return true;
    }

    public function visitPagePostPhoto(\App\Domain\Model\Common\PagePostPhoto $photo) {
        $post = $photo->commentedPost();
        if($post && ($post->isSoftlyDeleted() || $post->asPage()->isSoftlyDeleted())) {
            return false;
        }
        return true;
    }

    public function visitPagePhoto(\App\Domain\Model\Pages\Photos\AlbumPhoto $photo) {
        
    }

}