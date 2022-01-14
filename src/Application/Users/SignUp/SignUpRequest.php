<?php
declare(strict_types=1);
namespace App\Application\Users\SignUp;

class SignUpRequest implements \App\Application\BaseRequest {
    public ?string $requesterId;
    public $email;
    public $password;
    public $repeatedPassword;
    public $firstName;
    public $lastName;
    public $username;
    public $sex;
    public $birthday;
    public $language;

    public function __construct(?string $requesterId, $email, $password, $repeatedPassword, $firstName, $lastName, $username, $sex, $birthday, $language) {
        $this->requesterId = $requesterId;
        $this->email = $email;
        $this->password = $password;
        $this->repeatedPassword = $repeatedPassword;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->username = $username;
        $this->sex = $sex;
        $this->birthday = $birthday;
        $this->language = $language;
    }
}
