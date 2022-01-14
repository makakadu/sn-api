<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Comments\Video;

use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Comments\ProfileComment;
use App\Domain\Model\DomainException;
use App\Domain\Model\Users\Comments\Attachment;
use App\Domain\Model\Users\Comments\CommentAttachmentVisitor;

class Video extends Attachment {
    use EntityTrait;
    use \App\Domain\Model\Common\VideoTrait;
            
    /** @param array<string> $previews */
    function __construct(User $creator, string $hosting, string $link, array $previews) {
        parent::__construct($creator);
        $this->setPreviews($previews);
        $this->hosting = $hosting;
        $this->link = $link;
    }
    
//    public function setComment(ProfileComment $comment): void {
//        if(!$this->creator->equals($comment->creator())) { // Эту проверку можно сделать и раньше, но всё-таки это бизнес логика и её место здесь
//            throw new DomainException("Video can be added to comment if creator of comment and creator of video are same");
//        }
//        if($this->commentId && $this->commentId !== $comment->id()) {
//            throw new DomainException("Video cannot be added to another comment");
//        }
//        $this->commentId = $comment->id();
//    }

    public function acceptAttachmentVisitor( $visitor) {
        return $visitor->visitVideo($this);
    }

    public function type(): string {
        return 'video';
    }
    
    public function preview(): string {
        return $this->previewMedium();
    }
}
