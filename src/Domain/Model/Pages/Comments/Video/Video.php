<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Comments\Video;

use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Comments\Attachment;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Pages\Comments\CommentAttachmentVisitor;

class Video extends Attachment {
    use EntityTrait;
    use \App\Domain\Model\Common\VideoTrait;
            
    /** @param array<string> $previews */
    function __construct(User $creator, Page $owningPage, string $hosting, string $link, array $previews) {
        parent::__construct($creator, $owningPage);
        $this->setPreviews($previews);
        $this->hosting = $hosting;
        $this->link = $link;
    }

    public function acceptAttachmentVisitor(CommentAttachmentVisitor $visitor) {
        return $visitor->visitVideo($this);
    }

    public function type(): string {
        return "video";
    }

    public function preview(): string {
        return $this->previewMedium();
    }

}
