<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Comments;

/**
 * @template T
 */

interface AttachmentVisitor {
    
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
