<?php
declare(strict_types=1);
namespace App\DTO\Users;

class UserSmallDTO {
    
    public string $id;
    public ?string $picture;
    public string $firstName;
    public string $lastName;
    public string $username;
    
    function __construct(string $id, ?string $picture, string $firstName, string $lastName, string $username) {
        $this->id = $id;
        $this->picture = $picture;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->username = $username;
    }

}
