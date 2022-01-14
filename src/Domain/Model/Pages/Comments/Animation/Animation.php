<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Comments\Animation;

use App\Domain\Model\Common\AnimationTrait;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Comments\Attachment;
use App\Domain\Model\Pages\Comments\CommentAttachmentVisitor;
use App\Domain\Model\Pages\Page\Page;

class Animation extends Attachment {
    use EntityTrait;
    use AnimationTrait;
    
    function __construct(User $creator, Page $owningPage, string $preview) {
        parent::__construct($creator, $owningPage);
        $this->preview = $preview;
    }

    public function acceptAttachmentVisitor(CommentAttachmentVisitor $visitor) {
        return $visitor->visitAnimation($this);
    }

    public function type(): string {
        return 'animation';
    }
}
