<?php

declare(strict_types=1);

namespace App\Domain\Model;

interface AuthenticationService {
    
    function setCurrentUserId(string $id): void;

    function currentUserId(): ?string;

    /** @param array<string> $roles */
    function setCurrentUserRoles(array $roles): void;

    /**
     * @return array<string>
     */
    function currentUserRoles(): array;

    function isAuthenticated(): bool;
    
    function expiresIn(): ?int;
    
    function setExpiresIn(?int $expiresIn): void;
}
