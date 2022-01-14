<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Post\Animation;

use App\Domain\Model\Common\AnimationInterface;
use App\Domain\Model\Common\AnimationTrait;
use App\Domain\Model\Common\AnimationVisitor;
use App\Domain\Model\Common\AnimationVisitorVisitable;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\Post\Post;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Post\Attachment;
use App\Domain\Model\DomainException;
use App\Domain\Model\Users\Post\AttachmentVisitor;

class Animation extends Attachment {
    use EntityTrait;
    use AnimationTrait;
            
    function __construct(User $creator, string $preview, string $src) {
        parent::__construct($creator);
        $this->preview = $preview;
        $this->src = $src;
    }

    function owner(): User {
        return $this->creator;
    }

    function post(): ?Post {
        return $this->post;
    }
    
    public function setPost(?Post $post): void {
        if($post && !$this->creator->equals($post->creator())) { // Эту проверку можно сделать и раньше, но всё-таки это бизнес логика и её место здесь
            throw new DomainException("Animation can be added to post if creator of post and creator of animation are same");
        }
        if($this->post && $post && !$this->post->equals($post)) {
            throw new DomainException("Animation cannot be added to another post");
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
        return $visitor->visitAnimation($this);
    }

    public function type(): string {
        return 'animation';
    }

}
