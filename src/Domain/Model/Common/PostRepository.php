<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

use App\Domain\Model\Users\User\User;

interface PostRepository extends \App\Domain\Repository {
    
    /** @return array<PostInterface> */
    function getAllAccessibleToRequester(
        ?User $requester,
        ?string $offset,
        ?string $text,
        int $count,
        string $order,
        int $commentsCount,
        string $commentsOrder,
        string $commentsType,
        int $hideFromUsers,
        int $hideFromGroups,
        int $hideFromPages
    ): array;
}