<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Application\Exceptions\ForbiddenException;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Post\Post;
use App\Domain\Model\Groups\Photos\AlbumPhoto\AlbumPhoto;
use App\Domain\Model\Groups\Photos\AlbumPhoto\Comment\Comment;
use App\Application\Errors;
use App\Domain\Model\Pages\Photos\PagePicture\PagePicture;

class PagePicturesAuth {
    
    

    function canSee(User $requester, PagePicture $picture): bool {

    }
    
}