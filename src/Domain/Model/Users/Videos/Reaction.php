<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Videos;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Users\ProfileReaction;

class Reaction extends \App\Domain\Model\Users\ProfileReaction {
    
    private Video $video;
    
    function __construct(User $creator, Video $video, int $type) {//, ?Page $asPage) {
        $this->id = (string)\Ulid\Ulid::generate(true);
        $this->creator = $creator;
        $this->owner = $video->creator();
        $this->video = $video;
        
//        $this->asPage = $asPage;
//        $this->pageId = $asPage ? $asPage->id() : "";
        
        $this->changeReactionType($type);
        $this->createdAt = new \DateTime("now");
    }

    public function reacted(): \App\Domain\Model\Common\Reactable {
        return $this->video;
    }

}