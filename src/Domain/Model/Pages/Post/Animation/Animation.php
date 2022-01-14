<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Post\Animation;

use App\Domain\Model\Common\AnimationTrait;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Post\Attachment;
use App\Domain\Model\Pages\Post\AttachmentVisitor;
use App\Domain\Model\Pages\Page\Page;

class Animation extends Attachment {
    use EntityTrait;
    use AnimationTrait;
            
    function __construct(User $creator, Page $owningPage, string $src, string $preview) {
        parent::__construct($creator, $owningPage);
        $this->src = $src;
        $this->preview = $preview;
    }
    
    public function acceptAttachmentVisitor(AttachmentVisitor $visitor) {
        return $visitor->visitAnimation($this);
    }

    public function type(): string {
        return 'animation';
    }

}
