<?php //

namespace App\Application\Users\ConfirmSignUp;

use App\Domain\Model\Users\User\User;

class ConfirmSignUpResponse {
    public $id;
    public $firstName;
    public $lastName;

    function __construct(User $user) {
        $this->id = $user->id();
        $this->firstName = $user->firstName();
        $this->lastName = $user->lastName();
    }
}
