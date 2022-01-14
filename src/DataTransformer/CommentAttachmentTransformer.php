<?php
declare(strict_types=1);
namespace App\DataTransformer;

use App\Domain\Model\Common\Comments\Photo;
use App\Domain\Model\Common\Comments\Video;
use App\Domain\Model\Common\Comments\Animation;

use App\Domain\Model\Common\Comments\AttachmentVisitor;

use App\DTO\Common\AttachmentDTO;
use App\DTO\Common\PhotoAttachmentDTO;
use App\DTO\Common\VideoAttachmentDTO;
use App\DTO\Common\AnimationAttachmentDTO;

/**
 * @implements AttachmentVisitor<AttachmentDTO>
 */
class CommentAttachmentTransformer implements AttachmentVisitor {

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
        return new PhotoAttachmentDTO($photo->id(), $photo->preview());
    }

    /**
     * @return VideoAttachmentDTO
     */
    function visitVideo(Video $video) {
        return new VideoAttachmentDTO($video->id(), $video->link(), $video->preview());
    }
}