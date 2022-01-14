<?php
declare(strict_types=1);
namespace App\Application\Users\SignUp;

class SignUpResponse implements \App\Application\BaseResponse {

    public $message;

    function __construct(string $message) {
        $this->message = $message;
    }
}
