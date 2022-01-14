<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Post\Video;

use App\Domain\Model\Common\VideoTrait;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\Post\Post;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Post\Attachment;
use App\Domain\Model\DomainException;

class Video extends Attachment {
    use EntityTrait;
    use VideoTrait;
            
    /** @param array<string> $previews */
    function __construct(User $creator, string $hosting, string $link, array $previews) {
        parent::__construct($creator);
        $this->setPreviews($previews);
        $this->hosting = $hosting;
        $this->link = $link;
    }

    function post(): ?Post {
        return $this->post;
    }

    public function creator(): User {
        return $this->creator;
    }

    public function setPost(Post $post): void {
        if(!$this->creator->equals($post->creator())) { // Эту проверку можно сделать и раньше, но всё-таки это бизнес логика и её место здесь
            throw new DomainException("Video can be added to post if creator of post and creator of photo are same");
        }
        if($this->post && !$this->post->equals($post)) {
            throw new DomainException("Video cannot be added to another post");
        }
        $this->post = $post;
    }
    
    function isDeleted(): bool {
        return $this->isDeleted;
    }
    
    function isDeletedByGlobalManager(): bool {
        return $this->isDeletedByGlobalManager;
    }

    public function acceptAttachmentVisitor(\App\Domain\Model\Users\Post\AttachmentVisitor $visitor) {
        return $visitor->visitVideo($this);
    }

    public function type(): string {
        return 'video';
    }
}
