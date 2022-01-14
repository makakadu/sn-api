<?php
declare(strict_types=1);
namespace App\Application\Users;

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