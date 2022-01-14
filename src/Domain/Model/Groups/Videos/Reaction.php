<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Videos;

use App\Domain\Model\Users\User\User;

class Reaction extends \App\Domain\Model\Groups\GroupReaction {
    
    private Video $video;
  
    function __construct(User $creator, Video $video, string $type, bool $asGroup) {
        parent::__construct($creator, $video->owningGroup(), $type, $asGroup);
        $this->video = $video;
    }
    
    function video(): Video {
        return $this->video;
    }

    public function reacted(): \App\Domain\Model\Common\Reactable {
        return $this->video;
    }

}