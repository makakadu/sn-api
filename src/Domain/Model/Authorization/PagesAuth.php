<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Page\Page;

class PagesAuth {
    use AuthorizationTrait;
    
    function failIfCannotBan(User $initiator, Page $page, User $target): void {
        if(!$page->isAdminOrEditor($initiator)) {
            throw new \App\Application\Exceptions\ForbiddenException(\App\Application\Errors::NO_RIGHTS, "No rights to ban user");
        }
    }
    
    function failIfCannotCreateCommentOnBehalfOfPage(User $requester, Page $page): void {
        
    }
}