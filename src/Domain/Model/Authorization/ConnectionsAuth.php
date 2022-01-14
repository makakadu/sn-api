<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Connection\Connection;
use App\Domain\Model\Users\PrivacyService\PrivacyService;
use App\Application\Exceptions\ForbiddenException;

class ConnectionsAuth {
    use AuthorizationTrait;

    function failIfCannotOfferConnection(User $initiator, User $target): void {
        if($initiator->inBlacklist($target) || $target->inBlacklist($initiator)) {
            throw new ForbiddenException(123, "Cannot create connection while requester or target in blacklist");
        }
        //$this->failIfInBlacklist($user1, $user2, "Cannot create connection with user '{$user2->id()} because banned by this user");
    }
    
    function failIfCannotSeeConnectionsOf(User $requester, User $user): void {
        if($requester->equals($user)) {
            return;
        }
        // $this->privacy->isAllowedTo($requester, $user, '')

        $this->failIfInBlacklist($requester, $user, "Banned ");
    }
    
    function failIfCannotSee(User $requester, Connection $conn): void {
        if($requester->id() === $conn->initiatorId() || $requester->id() === $conn->targetId()) {
            return;
        }
        throw new \App\Application\Exceptions\ForbiddenException(\App\Application\Errors::NO_RIGHTS, 'No rights to see');
    }
    
    function failIfCannotDelete(User $requester, Connection $connection): void {
        if($requester->id() !== $connection->initiatorId() && $requester->id() !== $connection->targetId()) {
            throw new ForbiddenException(123, "Cannot delete connection {$connection->id()}. Only participants of connection can delete connection");
        }
    }
}
