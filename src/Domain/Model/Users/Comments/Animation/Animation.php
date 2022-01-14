<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Comments\Animation;

use App\Domain\Model\Common\AnimationTrait;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Comments\ProfileComment;
use App\Domain\Model\DomainException;
use App\Domain\Model\Users\Comments\Attachment;
use App\Domain\Model\Users\Comments\CommentAttachmentVisitor;

class Animation extends Attachment {
    use EntityTrait;
    use AnimationTrait;
    
    function __construct(User $creator, string $src, string $preview) {
        parent::__construct($creator);
        $this->src = $src;
        $this->preview = $preview;
        //$this->setVersions($versions);
    }

    public function setComment(ProfileComment $comment): void {
        if(!$this->creator->equals($comment->creator())) { // Эту проверку можно сделать и раньше, но всё-таки это бизнес логика и её место здесь
            throw new DomainException("Photo can be added to comment if creator of comment and creator of photo are same");
        }
        if($this->commentId() && $this->commentId() !== $comment->id()) {
            throw new DomainException("Photo cannot be added to another comment");
        }
        $this->commentId = $comment->id();
    }

    public function acceptAttachmentVisitor(CommentAttachmentVisitor $visitor) {
        return $visitor->visitAnimation($this);
    }

    public function type(): string {
        return 'animation';
    }
}
