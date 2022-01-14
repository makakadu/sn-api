<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Photos;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Page\Page;

class Reaction extends \App\Domain\Model\Pages\PageReaction {

    private Photo $photo;
    private bool $onBehalfOfPage;
    private ?string $pageId;
    
    function __construct(User $creator, Photo $photo, string $type, bool $onBehalfOfPage) {
        $this->id = (string)\Ulid\Ulid::generate(true);
        $this->changeReactionType($type);
        
        $this->createdAt = new \DateTime('now');
        $this->creator = $creator;
        $this->page = $photo->owningPage();
        $this->pageId = $onBehalfOfPage ? $this->owningPage->id() : null;
        $this->onBehalfOfPage = $onBehalfOfPage;
        $this->photo = $photo;
    }

    public function reacted(): \App\Domain\Model\Common\Reactable {
        return $this->photo;
    }

}