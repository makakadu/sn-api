<?php
declare(strict_types=1);
namespace App\DTO\Users;

class SubscriberDTO {
    
    public string $id;
    public ?array $picture;
    public string $firstName;
    public string $lastName;
    public string $username;
    
    function __construct(string $id, ?array $picture, string $firstName, string $lastName, string $username) {
        $this->id = $id;
        $this->picture = $picture;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->username = $username;
    }


}
