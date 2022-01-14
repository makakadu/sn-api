<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Shares;

use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Pages\Photos\AlbumPhoto\AlbumPhoto;
use App\Domain\Model\Users\User\User;

class SharedPageAlbumPhoto extends Shared {
    
    private ?AlbumPhoto $photo;
    private ?Page $page;
    private ?User $creator;
    private bool $onBehalfOfPage;
            
    function __construct(AlbumPhoto $photo) {
        $this->page = $photo->owningPage();
        $this->photo = $photo;
        $this->originalId = $photo->id();
        $this->creator = $photo->creator();
        $this->onBehalfOfPage = $photo->onBehalfOfPage();
        $this->createdAt = new \DateTime('now');
        $this->originalCreatedAt = $photo->createdAt();
    }

    public function acceptSharedVisitor(SharedVisitor $visitor) {
        return $visitor->visitSharedPageAlbumPhoto($this);
    }
    
    public function photo(): ?AlbumPhoto {
        return $this->photo;
    }

    public function page(): ?Page {
        return $this->page;
    }

    public function creator(): ?User {
        return $this->creator;
    }

    public function onBehalfOfPage(): bool {
        return $this->onBehalfOfPage;
    }

    public function shared(): ?Shareable {
        return $this->photo;
    }

}
