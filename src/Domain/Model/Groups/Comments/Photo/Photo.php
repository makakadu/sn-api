<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Comments\Photo;

use App\Domain\Model\Common\PhotoTrait;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Comments\Attachment;
use App\Domain\Model\Groups\Comments\CommentAttachmentVisitor;
use App\Domain\Model\Groups\Group\Group;

class Photo extends Attachment {
    use EntityTrait;
    use PhotoTrait;
            
    /** @param array<string> $versions */
    function __construct(User $creator, Group $owningGroup, array $versions) {
        parent::__construct($creator, $owningGroup);
        $this->setVersions($versions);
    }

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
