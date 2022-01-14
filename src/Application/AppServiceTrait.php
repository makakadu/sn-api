<?php
declare(strict_types=1);
namespace App\Application;

use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\User\User;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\Errors;
use App\Application\Exceptions\NotExistException;
use App\Application\Exceptions\ForbiddenException;
use App\Domain\Model\Pages\Page\PageRepository;

trait AppServiceTrait {
    
    protected UserRepository $users;
    
    protected function findUserOrFail(string $userId, bool $asTarget, ?string $message): User {
        $user = $this->users->getById($userId);
        if($user) {
            return $user;
        }
        $user = $this->users->getByUsername($userId);
        if($user) {
            return $user;
        }
        
        $message = $message ?? "User $userId not found";
        if($asTarget) {
            throw new NotExistException($message);
        } else {
            throw new UnprocessableRequestException(1, $message);
        }
    }
    
    function findUser(string $userId): ?User {
        return $this->users->getById($userId);
    }
    
    function findRequesterOrFailIfNotFoundOrInactive(string $requesterId): User {
        $requester = $this->findRequesterOrFail($requesterId);
        $this->failIfRequesterIsInactive($requester);
        
        return $requester;
    }
    
    function findRequesterOrFail(string $requesterId): User {
        $requester = $this->users->getById($requesterId);
        
        if(!$requester) {
            throw new UnprocessableRequestException(Errors::AUTHENTICATED_USER_NOT_FOUND, "AUTHENTICATED_USER_NOT_FOUND");
        }
        return $requester;
    }
    
    protected function failIfRequesterIsInactive(User $user): void {
        $message = fn(string $inactivityReason) => "Requester was $inactivityReason";
        $this->failIfInactive($user, $message);
    }

    protected function isRequesterInactive(User $user): bool {
        return $user->isSuspended() || $user->isDeleted() || $user->isBlocked();
    }
    
    protected function isUserInactive(User $user): bool {
        return $user->isSuspended() || $user->isDisabled() || $user->isBlocked();
    }
    
    protected function failIfInactive(User $user, callable $message): void {
        if($this->isUserInactive($user)) {
            $inactivityReason = $this->getReasonOfUserInactivity($user);
            throw new ForbiddenException(11, $message($inactivityReason));
        }
    }
    
    protected function getReasonOfUserInactivity(User $user): ?string {
        if($user->isSuspended()) {
            return 'suspended';
        } elseif($user->isBlocked()) {
            return 'blocked forever';
        } elseif(!$user->isActivated()) {
            return 'not activated';
        }
        return null;
    }
    
}