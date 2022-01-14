<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Videos\Video;
use App\Application\Exceptions\ForbiddenException;
use App\Application\Errors;

class UserVideosAuth {
    use AuthorizationTrait;
    
    public function __construct(\App\Domain\Model\Users\PrivacyService\PrivacyResolver_new $privacy) {
        $this->privacy = $privacy;
    }
    
    function canSee(User $requester, Video $video): bool {
        return true;
    }

    function failIfCannotSee(User $requester, Video $video): void {
        if($requester->equals($video->creator())) {
            return;
        }
        $this->failIfSoftlyDeleted($video);
        
        if(!$this->privacy->hasAccess($requester, $video->whoCanSee())) {
            throw new ForbiddenException(Errors::PROHIBITED_BY_PRIVACY, "Forbidden by video privacy settings");
        }
    }

    function failIfCannotComment(User $requester, Video $video): void {
        if(!$this->privacy->hasAccess($video->whoCanComment(), $requester)) {
            throw new ForbiddenException(Errors::PROHIBITED_BY_PRIVACY, "Forbidden by video privacy settings");
        }
    }
    
    function failIfGuestsCannotSee(Video $video): void {
        $this->failIfSoftlyDeleted($video);
        
        if(!$this->privacy->guestsHaveAccess($video->whoCanSee())) {
            throw new ForbiddenException(Errors::PROHIBITED_BY_PRIVACY, "Forbidden by video privacy settings");
        }
    }
    
    function failIfCannotEdit(User $requester, Video $video) {
        if(!$video->creator()->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot edit video from not own profile");
        }
    }
    
    function failIfCannotEditComment(User $requester, Comment $comment) {
        if(!$comment->creator()->equals($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot edit comment created by another user");
        }
    }

    function failIfSoftlyDeleted(Video $video): void {
        if($video->isSoftlyDeleted()) {
            throw new ForbiddenException(Errors::SOFTLY_DELETED, "Forbidden");
        }
    }
}