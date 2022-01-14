<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Comments\Photo;

use App\Domain\Model\Common\PhotoInterface;
use App\Domain\Model\Common\PhotoTrait;
use App\Domain\Model\Common\PhotoVisitor;
use App\Domain\Model\Common\PhotoVisitorVisitable;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Comments\Attachment;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Pages\Comments\CommentAttachmentVisitor;

class Photo extends Attachment implements PhotoInterface, PhotoVisitorVisitable {
    use EntityTrait;
    use PhotoTrait;
            
    /** @param array<string> $versions */
    function __construct(User $creator, Page $owningPage, array $versions) {
        parent::__construct($creator, $owningPage);
        $this->setVersions($versions);
    }

    public function accept(PhotoVisitor $visitor) {
        
    }
    // Аннотации берутся из интерфейса Attachment
    public function acceptAttachmentVisitor(CommentAttachmentVisitor $visitor) {
        return $visitor->visitPhoto($this);
    }

    public function type(): string {
        return "photo";
    }
    
    public function preview(): string {
        return $this->medium();
    }

    public function src(): string {
        return $this->original();
    }

}
