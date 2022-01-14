<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\User;

class Username {
    private string $username;

    public function __construct(string $username) {
        $this->username = $username;
    }

    public function username(): string {
        return $this->username;
    }

    public function equals(Username $username): bool {
        return $this->username === $username->username;
    }

    public function __toString() {
        return $this->username;
    }
}
