<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Post;

use App\Domain\Repository;
use App\Domain\Model\Users\User\User;

interface PostRepository extends Repository {
    function getById(string $id): ?Post;
    /** @return array<Post> */
    
    function getByOwnerId(string $id, ?string $ofsset, int $count): array;
    
    /** @return array<int, Post> */
    function getPartOfNotDeletedByOwner(User $owner, ?string $offsetId, int $count): array;
    
    function getCountOfActiveByOwner(User $owner): int;
    
    function getPartOfActiveAndAccessibleToRequesterByOwner(?User $requester, User $owner, ?string $offsetId, int $count, string $order);
    
    function getCountOfActiveAndAccessibleToRequesterByOwner(?User $requester, User $owner);

}
