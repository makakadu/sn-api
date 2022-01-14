<?php
declare(strict_types=1);
namespace App\Application;

use App\Domain\Model\Common\Comments\CommentRepository;

class CheckPhotoIsActiveVisitor implements \App\Domain\Model\Common\PhotoVisitor {
    private CommentRepository $comments;
    
    function __construct(CommentRepository $comments) {
        $this->comments = $comments;
    }
    
    function visitUserPhoto(\App\Domain\Model\Users\Photos\Photo $photo) {
        if($photo->isDeleted() || $photo->owner()->isDeleted()) {
            return false;
        }
        if($photo->commentId()) {
            $comment = $this->comments->getById($comment);
            $commented = $comment->commented();
            $commentedOwner = $commented->owner();
            
            if($comment->isDeleted() || $commented->isSoftlyDeleted() || $commentedOwner->isSoftlyDeleted()) {
                return false;
            }
        }
        elseif($photo->post() && $photo->post->isSoftlyDeleted()) {
            return false;
        }
        elseif($photo->album() && $photo->album()->isSoftlyDeleted()) {
            return false;
        }
        return true;
    }
    
    function visitGroupPhoto(\App\Domain\Model\Groups\Photos\Photo $photo) {
        if($photo->isDeleted() || $photo->group()->isSoftlyDeleted()) {
            return false;
        }
        if($photo->commentId()) {
            $comment = $this->comments->getById($comment);
            $commented = $comment->commented();
            $commentedGroup = $commented->group();
            
            if($comment->isDeleted() || $commented->isSoftlyDeleted() || $commentedGroup->isSoftlyDeleted()) {
                return false;
            }
        }
        elseif($photo->post() && $photo->post->isSoftlyDeleted()) {
            return false;
        }
        elseif($photo->album() && $photo->album()->isSoftlyDeleted()) {
            return false;
        }
        return true;
    }
    
    function visitPagePhoto(\App\Domain\Model\Pages\Photos\AlbumPhoto $photo) {
        if($photo->isSoftlyDeleted() || $photo->onBehalfOfPage()->isSoftlyDeleted()) {
            return false;
        }
        if($photo->commentId()) {
            $comment = $this->comments->getById($comment);
            $commented = $comment->commented();
            $commentedPage = $commented->asPage();
            
            if($comment->isDeleted() || $commented->isSoftlyDeleted() || $commentedPage->isSoftlyDeleted()) {
                return false;
            }
        }
        elseif($photo->post() && $photo->post()->isSoftlyDeleted()) {
            return false;
        }
        elseif($photo->album() && $photo->album->isSoftlyDeleted()) {
            return false;
        }
        return true;
    }

}