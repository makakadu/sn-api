<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Post\Photo;

use App\Domain\Model\Common\PhotoInterface;
use App\Domain\Model\Common\PhotoTrait;
use App\Domain\Model\Common\PhotoVisitorVisitable;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Post\Attachment;
use App\Domain\Model\Groups\Post\AttachmentVisitor;
use App\Domain\Model\Groups\Group\Group;

class Photo extends Attachment {
    use EntityTrait;
    use PhotoTrait;
            
    /** @param array<string> $versions */
    function __construct(User $creator, Group $owningGroup, array $versions) {
        parent::__construct($creator, $owningGroup);
        $this->setVersions($versions);
    }
    
    public function acceptAttachmentVisitor(AttachmentVisitor $visitor) {
        return $visitor->visitPhoto($this);
    }

    public function type(): string {
        return 'photo';
    }
}
