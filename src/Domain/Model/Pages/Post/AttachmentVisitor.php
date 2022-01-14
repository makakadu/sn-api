<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Post;

use App\Domain\Model\Pages\Post\Photo\Photo;
use App\Domain\Model\Pages\Post\Video\Video;
use App\Domain\Model\Pages\Post\Animation\Animation;

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
