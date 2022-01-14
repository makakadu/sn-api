<?php
declare(strict_types=1);
namespace App\Application\Users;

use App\Domain\Model\Users\Comments\Photo\Photo as ProfileCommentPhoto;
use App\Domain\Model\Users\Comments\Video\Video as ProfileCommentVideo;
use App\DTO\CommentAttachmentDTO;

/**
 * @implements CommentAttachmentVisitor<void>
 */
class CommentAttachmentVisitorImpl implements CommentAttachmentVisitor {

    /**
     * @return void
     */
    function visitProfileCommentPhoto(ProfileCommentPhoto $photo) {
        
    }

    /**
     * @return void
     */
    function visitProfileCommentVideo(ProfileCommentVideo $video) {
        
    }
}