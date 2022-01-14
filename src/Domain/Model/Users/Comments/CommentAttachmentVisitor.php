<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Comments;

use App\Domain\Model\Users\Comments\Animation\Animation;
use App\Domain\Model\Users\Comments\Photo\Photo;
use App\Domain\Model\Users\Comments\Video\Video;

/**
 * @template T
 */
interface CommentAttachmentVisitor {
    
    /**
     * @return T
     */
    function visitAnimation(Animation $animation);
    
    /**
     * @return T
     */
    function visitPhoto(Photo $photo);
    
    /**
     * @return T
     */
    function visitVideo(Video $video);

}