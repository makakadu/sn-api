<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;

use App\Domain\Model\Users\Post\Attachment as PostAttachment;
use App\Domain\Model\Users\Post\AttachmentVisitor;
use App\DTO\Common\AttachmentDTO;
use App\DTO\Common\AnimationAttachmentDTO;
use App\DTO\Common\PhotoAttachmentDTO;
use App\DTO\Common\VideoAttachmentDTO;
use App\Domain\Model\Users\Post\Animation\Animation;
use App\Domain\Model\Users\Post\Photo\Photo;
use App\Domain\Model\Users\Post\Video\Video;
use Doctrine\Common\Collections\Collection;

/**
 * @implements AttachmentVisitor<AttachmentDTO>
 */

class PostAttachmentsTransformer extends Transformer implements AttachmentVisitor {
    use \App\DataTransformer\TransformerTrait;
    
    /**
     * @param Collection<string, PostAttachment> $attachments
     * @return array<int, AttachmentDTO>
     */
    function transform(Collection $attachments): array {
        $transformed = [];
        foreach ($attachments as $attachment) {
            $transformed[] = $attachment->acceptAttachmentVisitor($this);
        }
        return $transformed;
    }

    /**
     * @return AnimationAttachmentDTO
     */
    public function visitAnimation(Animation $animation) {
        return new AnimationAttachmentDTO(
            $animation->id(),
            $animation->src(),
            $animation->preview()
        );
    }
    
    /**
     * @return PhotoAttachmentDTO
     */
    public function visitPhoto(Photo $photo) {
        return new \App\DTO\Users\PostPhotoDTO(
            $photo->id(),
            $photo->versions(),
            $this->creatorToDTO($photo->owner()),
            $this->creationTimeToTimestamp($photo->createdAt()),
            $photo->post() ? $photo->post()->id() : null
        );
    }
    
    /**
     * @return VideoAttachmentDTO
     */
    public function visitVideo(Video $video) {
        return new VideoAttachmentDTO(
            $video->id(),
            $video->link(),
            $video->previewMedium()
        );
    }

}