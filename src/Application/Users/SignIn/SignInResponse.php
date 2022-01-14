<?php
declare(strict_types=1);
namespace App\Application\Users\SignIn;

class SignInResponse implements \App\Application\BaseResponse {
    
    public $message;
    public $jwt;
    public $token_type;
    public $expires_in;
    
    public function __construct($message, $jwt, $token_type, $expires_in) {
        $this->message = $message;
        $this->jwt = $jwt;
        $this->token_type = $token_type;
        $this->expires_in = $expires_in;
    }

}
