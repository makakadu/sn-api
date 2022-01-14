<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Shares;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Videos\Video;

class SharedUserVideo extends Shared {
    
    private ?Video $video;
    private ?User $creator;
            
    function __construct(Video $video) {
        $this->creator = $video->creator();
        $this->video = $video;
        $this->originalId = $video->id();
        $this->createdAt = new \DateTime('now');
        $this->originalCreatedAt = $video->createdAt();
    }

    public function acceptSharedVisitor(SharedVisitor $visitor) {
        return $visitor->visitSharedUserVideo($this);
    }
    
    public function creator(): ?User {
        return $this->creator;
    }
    
    public function video(): ?Video {
        return $this->video;
    }
    
    public function link(): ?string {
        return $this->video ? $this->video->link() : null;
    }
    
    public function preview(): ?string {
        return $this->video ? $this->video->previewSmall() : null;
    }

    public function shared(): ?Shareable {
        return $this->video;
    }

}
