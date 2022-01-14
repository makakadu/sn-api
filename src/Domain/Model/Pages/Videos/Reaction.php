<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Videos;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Page\Page;

class Reaction extends \App\Domain\Model\Pages\PageReaction {
    
    private Video $video;
    private bool $onBehalfOfPage;
    private ?string $pageId;
    
    function __construct(User $creator, Video $video, string $type, bool $onBehalfOfPage) {
        $this->id = (string)\Ulid\Ulid::generate(true);
        $this->creator = $creator;
        $this->owningPage = $video->owningPage();
        $this->video = $video;
        $this->onBehalfOfPage = $onBehalfOfPage;
        $this->pageId = $onBehalfOfPage ? $this->owningPage->id() : null;
        $this->changeReactionType($type);
    }

    public function reacted(): \App\Domain\Model\Common\Reactable {
        return $this->video;
    }

}