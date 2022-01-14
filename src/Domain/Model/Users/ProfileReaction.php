<?php
declare(strict_types=1);
namespace App\Domain\Model\Users;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Page\Page;

abstract class ProfileReaction extends \App\Domain\Model\Common\Reaction {
    
    protected User $owner;
//    protected ?Page $asPage;
//    protected string $pageId;
    
    function owner(): User {
        return $this->owner;
    }
    
    function onBehalfOfPage(): ?Page {
        //return $this->asPage;
    }
}
