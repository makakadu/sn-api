<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Comments\Video;

use App\Domain\Model\Common\VideoTrait;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Comments\Attachment;
use App\Domain\Model\Groups\Comments\CommentAttachmentVisitor;
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

    public function acceptAttachmentVisitor(CommentAttachmentVisitor $visitor) {
        return $visitor->visitVideo($this);
    }

    public function type(): string {
        return 'animation';
    }
    
    public function preview(): string {
        return $this->previewMedium();
    }
}
