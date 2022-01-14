<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Post\Photo;

use App\Domain\Model\Common\PhotoInterface;
use App\Domain\Model\Common\PhotoTrait;
use App\Domain\Model\Common\PhotoVisitorVisitable;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Post\Attachment;
use App\Domain\Model\Pages\Post\AttachmentVisitor;
use App\Domain\Model\Pages\Page\Page;

class Photo extends Attachment {
    use EntityTrait;
    use PhotoTrait;
            
    /** @param array<string> $versions */
    function __construct(User $creator, Page $owningPage, array $versions) {
        parent::__construct($creator, $owningPage);
        $this->setVersions($versions);
    }
    
    public function acceptAttachmentVisitor(AttachmentVisitor $visitor) {
        return $visitor->visitPhoto($this);
    }

    public function type(): string {
        return 'photo';
    }
}
