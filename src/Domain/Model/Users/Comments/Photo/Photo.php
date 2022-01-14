<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Comments\Photo;

use App\Domain\Model\Common\PhotoInterface;
use App\Domain\Model\Common\PhotoTrait;
use App\Domain\Model\Common\PhotoVisitor;
use App\Domain\Model\Common\PhotoVisitorVisitable;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Comments\ProfileComment;
use App\Domain\Model\DomainException;
use App\Domain\Model\Users\Comments\Attachment;
use App\Domain\Model\Users\Comments\CommentAttachmentVisitor;

class Photo extends Attachment {
    use EntityTrait;
    use PhotoTrait;

    /** @param array<string> $versions */
    function __construct(User $creator, array $versions) {
        parent::__construct($creator);
        $this->setVersions($versions);
    }
    
//    public function setComment(ProfileComment $comment): void {
//        if(!$this->creator->equals($comment->creator())) { // Эту проверку можно сделать и раньше, но всё-таки это бизнес логика и её место здесь
//            throw new DomainException("Photo can be added to comment if creator of comment and creator of photo are same");
//        }
//        if($this->commentId && $this->commentId !== $comment->id()) {
//            throw new DomainException("Photo cannot be added to another comment");
//        }
//        $this->commentId = $comment->id();
//    }

    // Аннотации берутся из интерфейса Attachment
    public function acceptAttachmentVisitor(CommentAttachmentVisitor $visitor) {
        return $visitor->visitPhoto($this);
    }

    public function type(): string {
        return 'photo';
    }

    public function preview(): string {
        return $this->medium();
    }

    public function src(): string {
        return $this->original();
    }
    
}
