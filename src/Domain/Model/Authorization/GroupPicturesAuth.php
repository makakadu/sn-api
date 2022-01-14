<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Application\Exceptions\ForbiddenException;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Post\Post;
use App\Domain\Model\Groups\Photos\AlbumPhoto\AlbumPhoto;
use App\Domain\Model\Groups\Photos\AlbumPhoto\Comment\Comment;
use App\Application\Errors;

class GroupPicturesAuth {

    function canSee(User $requester, \App\Domain\Model\Groups\Photos\GroupPicture\GroupPicture $picture): bool {

    }
    
    function failIfCannotSee(User $requester, \App\Domain\Model\Groups\Photos\GroupPicture\GroupPicture $picture): void {

    }
    
}