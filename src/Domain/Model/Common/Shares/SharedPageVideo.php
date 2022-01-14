<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Shares;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Videos\Video;
use App\Domain\Model\Pages\Page\Page;

class SharedPageVideo extends Shared {
    
    private ?Video $video;
    private ?User $creator;
    private ?Page $page;
    private bool $onBehalfOfPage;
    
    function __construct(Video $video) {
        $this->creator = $video->creator();
        $this->video = $video;
        $this->page = $video->owningPage();
        $this->onBehalfOfPage = $video->onBehalfOfPage();
        $this->originalId = $video->id();
        $this->createdAt = new \DateTime('now');
        $this->originalCreatedAt = $video->createdAt();
    }

    public function acceptSharedVisitor(SharedVisitor $visitor) {
        return $visitor->visitSharedPageVideo($this);
    }
    
    public function video(): ?Video {
        return $this->video;
    }

    public function page(): ?Page {
        return $this->page;
    }
    
    public function onBehalfOfPage(): bool {
        return $this->onBehalfOfPage;
    }

    function creator(): ?User {
        return $this->creator;
    }

    public function shared(): ?Shareable {
        return $this->video;
    }
}