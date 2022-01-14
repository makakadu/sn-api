<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Videos;

use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Collection;

interface VideoRepository extends \App\Domain\Repository {
    function getById(string $id): ?Video;
    function creator(): User;
    function isAllowedByPrivacyTo(User $user, string $action): bool;
    function commentsAreDisabled(): bool;
    function enableComments(): void;
    function disableComments(): void;
    function addComment(User $user, string $comment): void;
    function removeComment(string $commentId): void;
}
