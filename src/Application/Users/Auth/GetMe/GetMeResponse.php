<?php
declare(strict_types=1);
namespace App\Application\Users\Auth\GetMe;

use App\Application\BaseResponse;

class GetMeResponse implements BaseResponse {
    public string $id;
    public string $username;
    public string $firstName;
    public string $lastName;
    public string $email;
    public ?string $picture;
    public int $lastRequestsCheck;
    
    public function __construct(string $id, string $username, string $firstName, string $lastName, string $email, ?string $picture, int $lastRequestsCheck) {
        $this->id = $id;
        $this->username = $username;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->picture = $picture;
        $this->lastRequestsCheck = $lastRequestsCheck;
    }


}
