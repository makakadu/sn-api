<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Post\Video;

use App\Domain\Model\Common\VideoTrait;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Post\Attachment;
use App\Domain\Model\Groups\Post\AttachmentVisitor;
use App\Domain\Model\Groups\Group\Group;

class Video extends Attachment {
    use EntityTrait;
    use VideoTrait;
            
    /** @param array<string> $previews */
    function __construct(User $creator, Group $owningGroup, string $hosting, string $link, array $previews) {
        parent::__construct($creator, $owningGroup);
        $this->setPreviews($previews);
        $this->hosting = $hosting;
        $this->link = $link;
    }
    
    public function acceptAttachmentVisitor(AttachmentVisitor $visitor) {
        return $visitor->visitVideo($this);
    }

    public function type(): string {
        return 'video';
    }
    
    function link(): string {
        return $this->link;
    }
    
}
