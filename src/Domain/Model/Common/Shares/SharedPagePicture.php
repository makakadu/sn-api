<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Shares;

use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Pages\Photos\PagePicture\PagePicture;

class SharedPagePicture extends Shared {
    
    private ?PagePicture $picture;
    private ?Page $page;
            
    function __construct(PagePicture $picture) {
        $this->page = $picture->owningPage();
        $this->picture = $picture;
        $this->originalId = $picture->id();
        $this->createdAt = new \DateTime('now');
        $this->originalCreatedAt = $picture->createdAt();
    }

    public function acceptSharedVisitor(SharedVisitor $visitor) {
        return $visitor->visitSharedPagePicture($this);
    }
    
    public function picture(): ?PagePicture {
        return $this->picture;
    }

    public function page(): ?Page {
        return $this->page;
    }

    public function shared(): ?Shareable {
        return $this->picture;
    }

}
