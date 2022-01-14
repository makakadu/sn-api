<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Post\Photo;

use App\Domain\Model\Common\PhotoInterface;
use App\Domain\Model\Common\PhotoTrait;
use App\Domain\Model\Common\PhotoVisitor;
use App\Domain\Model\Common\PhotoVisitorVisitable;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\Post\Post;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Post\Attachment;
use App\Domain\Model\DomainException;
use App\Domain\Model\Users\Post\AttachmentVisitor;

class Photo extends Attachment implements PhotoInterface, PhotoVisitorVisitable {
    use EntityTrait;
    use PhotoTrait;
    
    /** @param array<string> $versions */
    function __construct(User $creator, array $versions) {
        parent::__construct($creator);
        $this->setVersions($versions);
    }
    
    public function setPost(?Post $post): void {
        if($post && !$this->creator->equals($post->creator())) { // Эту проверку можно сделать и раньше, но всё-таки это бизнес логика и её место здесь
            throw new DomainException("Photo can be added to post if creator of post and creator of photo are same");
        }
        if($this->post && $post && !$this->post->equals($post)) {
            throw new DomainException("Photo cannot be added to another post");
        }
        $this->post = $post;
    }
    
    function isDeleted(): bool {
        return $this->isDeleted;
    }
    
    function isDeletedByGlobalManager(): bool {
        return $this->isDeletedByGlobalManager;
    }

    public function acceptAttachmentVisitor(AttachmentVisitor $visitor) {
        return $visitor->visitPhoto($this);
    }

    public function accept(PhotoVisitor $visitor) {
        
    }

    public function type(): string {
        return 'photo';
    }

}
