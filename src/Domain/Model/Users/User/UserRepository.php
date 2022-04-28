<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\User;

use App\Domain\Repository;
use App\Domain\Model\Users\User\User;

interface UserRepository extends Repository {
    function getById(string $id): ?User;
    /** @return array<User> */
    public function getByFirstName(string $firstName);
    public function getByEmail(string $email): ?User;
    //public function getFriendsOf(int $userId, int $limit);

    public function getByUsername(string $username): ?User;
}
