<?php
declare(strict_types=1);
namespace App\DTO\Users;

abstract class ProfileDTO {
    
    public string $id;
    public string $firstName;
    public string $lastName;
    public string $username;
    
    public function __construct(string $id, string $firstName, string $lastName, string $username) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->username = $username;
    }

}
