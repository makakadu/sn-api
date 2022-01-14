<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Albums\Album;
use App\Domain\Model\Users\Photos\AlbumPhoto\AlbumPhoto;
use App\Application\Errors;
use App\Application\Exceptions\ForbiddenException;
use App\Domain\Model\Users\Photos\Comment\Comment;
use App\Domain\Model\Users\AccessLevels as AL;
use App\Domain\Model\Users\Photos\Reaction;
use App\Domain\Model\Users\Photos\ProfilePicture\ProfilePicture;

class ProfilePicturesAuth {
    use AuthorizationTrait;
    
    public function __construct(\App\Domain\Model\Users\PrivacyService\PrivacyResolver_new $privacy) {
        $this->privacy = $privacy;
    }

    function canSee(User $requester, ProfilePicture $picture): bool {
        
    }
    
    function failIfCannotShare(User $requester, ProfilePicture $picture): void {
        $this->failIfUserIsInactive($picture->owner(), "Cannot share profile picture, owner is %");
        $this->failIfSoftlyDeleted($picture, "Cannot share profile picture, picture in trash");
        $owner = $picture->owner();
        $this->privacy->hasAccess($requester, $owner->getPrivacySetting('pictures'));
    }
    
    function failIfCannotSee(User $requester, ProfilePicture $picture): void {
    }
}