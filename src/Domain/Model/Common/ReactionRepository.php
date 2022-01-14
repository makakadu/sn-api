<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

use App\Domain\Model\Users\User\User;

interface ReactionRepository {
    
    /**
     * @param array<int,string> $types
     * @return array<int,Reactable>
     */
    function getPartCreatedOnBehalfOfUser(User $reactor, int $count, ?string $offsetId, array $types): array;
}
