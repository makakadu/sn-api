<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Comments;

use App\Domain\Model\Pages\Comments\Animation\Animation;
use App\Domain\Model\Pages\Comments\Photo\Photo;
use App\Domain\Model\Pages\Comments\Video\Video;

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