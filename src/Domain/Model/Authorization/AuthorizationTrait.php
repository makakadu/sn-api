<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Domain\Model\Users\PrivacyService\PrivacyService;
use App\Domain\Model\Users\User\User;
use App\Application\Errors;
use App\Application\Exceptions\ForbiddenException;
use App\Application\Exceptions\NotExistException;

trait AuthorizationTrait {
    protected \App\Domain\Model\Users\PrivacyService\PrivacyResolver_new $privacy;
    
    protected function failIfCannotAccessProfile(User $requester, User $owner): void {
        if($requester->equals($owner)) {
            return;
        }
        $this->failIfInBlacklist($requester, $owner, "Banned by posts owner");
        
//        if(!$this->privacy->isProfileAccessibleTo($requester, $owner)) {
//            $this->throwPrivacyException();
//        }
    }
    
    function failIfUserIsDeleted(User $user, string $message): void {
        if($user->isDeleted()) {
            throw new NotExistException($message);
        }
    }
    
    protected function failIfGuestsCannotAccessProfile(User $owner): void {
        if(!$this->privacy->guestsHaveAccessToProfile($owner)) {
            $this->throwPrivacyException();
        }
    }
    
    protected function failIfBannedByResourceOwner(User $requester, User $owner): void {
        if($owner->inBlacklist($requester)) {
            throw new ForbiddenException(Errors::BANNED_BY_USER, "Banned by resource owner");
        }
    }
    
    public function failIfInBlacklist(User $requester, User $owner, string $message): void {
        if($owner->inBlacklist($requester)) {
            throw new ForbiddenException(Errors::BANNED_BY_USER, $message);
        }
    }

    protected function failIfNoRights(User $initiator, User $owner, string $message): void {
        if(!$initiator->equals($owner)) { throw new ForbiddenException(Errors::NO_RIGHTS, $message); }
    }
    
    protected function throwPrivacyException(string $message = "Prohibited by privacy settings"): void {
        throw new ForbiddenException(Errors::PROHIBITED_BY_PRIVACY, $message);
    }

    protected function inBannedList(User $requestee, User $requester): bool {
       return $requestee->inBlacklist($requester);
    }
    
    protected function failIfUserIsInactive(User $user, string $message): void {
        $inactivityReason = "";
        
        if($user->isBlocked()) { // Если временно забанен
            $inactivityReason = $message . "temporary banned";
        }
        elseif($user->isDeactivated()) { // Если деактивировал аккаунт
            $inactivityReason = $message . "deactivated";
        }
        if($inactivityReason) {
            throw new ForbiddenException(Errors::INACTIVE, $inactivityReason);
        }
        
    }
    
//    protected function failIfUserIsInactive(User $user, string $as): void {
//        if($this->isUserInactive($user)) {
//            $inactivityReason = $this->getReasonOfUserInactivity($user);
//            throw new ForbiddenException(Errors::INACTIVE, "Access to resource forbidden, $as was $inactivityReason");
//        }
//    }
    
    protected function isUserInactive(User $user): bool {
        return $user->isSuspended() || $user->isDisabled() || $user->isBlocked();
    }
    
    protected function getReasonOfUserInactivity(): ?string {
        return null;
    }
}
