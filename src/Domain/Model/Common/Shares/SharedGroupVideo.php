<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Shares;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Videos\Video;
use App\Domain\Model\Groups\Group\Group;

class SharedGroupVideo extends Shared {
    
    private ?Video $video;
    private ?User $creator;
    private ?Group $group;
    private bool $onBehalfOfGroup;
    
    function __construct(Video $video) {
        $this->creator = $video->creator();
        $this->video = $video;
        $this->group = $video->owningGroup();
        $this->onBehalfOfGroup = $video->onBehalfOfGroup();
        $this->originalId = $video->id();
        $this->createdAt = new \DateTime('now');
        $this->originalCreatedAt = $video->createdAt();
    }

    public function acceptSharedVisitor(SharedVisitor $visitor) {
        return $visitor->visitSharedGroupVideo($this);
    }
    
    public function video(): ?Video {
        return $this->video;
    }

    public function group(): ?Group {
        return $this->group;
    }
    
    public function onBehalfOfGroup(): bool {
        return $this->onBehalfOfGroup;
    }
    
    public function creator(): ?User {
        return $this->creator;
    }

    public function shared(): ?Shareable {
        return $this->video;
    }

}