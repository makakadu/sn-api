<?php
declare(strict_types=1);
namespace App\DataTransformer\Pages;

use App\Domain\Model\Pages\Comments\Photo\Photo;
use App\Domain\Model\Pages\Comments\Video\Video;
use App\Domain\Model\Pages\Comments\Animation\Animation;
use App\DTO\Common\AttachmentDTO;
use App\Domain\Model\Pages\Comments\CommentAttachmentVisitor;

use App\DTO\Common\PhotoAttachmentDTO;
use App\DTO\Common\VideoAttachmentDTO;
use App\DTO\Common\AnimationAttachmentDTO;

/**
 * @implements CommentAttachmentVisitor<AttachmentDTO>
 */
class CommentAttachmentTransformer implements CommentAttachmentVisitor {

    /**
     * @return AnimationAttachmentDTO
     */
    function visitAnimation(Animation $animation) {
        return new AnimationAttachmentDTO($animation->id(), $animation->src(), $animation->preview());
    }

    /**
     * @return PhotoAttachmentDTO
     */
    function visitPhoto(Photo $photo) {
        return new PhotoAttachmentDTO($photo->id(), $photo->medium());
    }

    /**
     * @return VideoAttachmentDTO
     */
    function visitVideo(Video $video) {
        return new VideoAttachmentDTO($video->id(), $video->link(), $video->preview());
    }
}