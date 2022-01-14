<?php
declare(strict_types=1);
namespace App\Application\Users\SignIn;

class SignInRequest implements \App\Application\BaseRequest {

    public ?string $requesterId;
    public $email;
    public $password;

    public function __construct(?string $requesterId, $email, $password) {
        $this->requesterId = $requesterId;
        $this->email = $email;
        $this->password = $password;
    }
}
