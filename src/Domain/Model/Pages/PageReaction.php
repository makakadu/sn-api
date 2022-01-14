<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages;

use App\Domain\Model\Pages\Page\Page;

abstract class PageReaction extends \App\Domain\Model\Common\Reaction {
    
    protected Page $owningPage;
//    protected ?Page $asPage;
//    protected string $pageId;
    
    function owningPage(): Page {
        return $this->owningPage;
    }
//    
//    function onBehalfOfPage(): ?Page {
//        return $this->asPage;
//    }
}
