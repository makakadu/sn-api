<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Comments\Animation;

use App\Domain\Model\Common\AnimationTrait;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Comments\Attachment;
use App\Domain\Model\Groups\Comments\CommentAttachmentVisitor;
use App\Domain\Model\Groups\Group\Group;

class Animation extends Attachment {
    use EntityTrait;
    use AnimationTrait;
    
    function __construct(User $creator, Group $owningGroup, string $preview) {
        parent::__construct($creator, $owningGroup);
        $this->preview = $preview;
    }

    public function acceptAttachmentVisitor(CommentAttachmentVisitor $visitor) {
        return $visitor->visitAnimation($this);
    }

    public function type(): string {
        return 'animation';
    }
}
