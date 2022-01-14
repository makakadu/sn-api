<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Post;

use App\Domain\Model\Users\Post\Photo\Photo;
use App\Domain\Model\Users\Post\Video\Video;
use App\Domain\Model\Users\Post\Animation\Animation;

/**
 * @template T
 */
interface AttachmentVisitor {
    /**
     * @return T
     */
    function visitPhoto(Photo $photo);
    
    /**
     * @return T
     */
    function visitVideo(Video $video);
    
    /**
     * @return T
     */
    function visitAnimation(Animation $animation);
}
