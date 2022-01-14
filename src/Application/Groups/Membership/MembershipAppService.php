<?php
declare(strict_types=1);
namespace App\Application\Groups\Membership;

use App\Domain\Model\Groups\Membership\MembershipRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Groups\Membership\Membership;

trait MembershipAppService {
    
    private MembershipRepository $memberships;

    function findMembershipOrFail(string $membershipId, bool $asTarget): ?Membership {
        $membership = $this->memberships->getById($membershipId);
        
        $found = true;
        if(!$membership) {
            $found = false;
        }
        if(!$found && $asTarget) {
            throw new NotExistException("Membership $membershipId not found");
        } elseif(!$found && !$asTarget) {
            throw new UnprocessableRequestException("Membership $membershipId not found");
        }
        return $membership;
    }

}