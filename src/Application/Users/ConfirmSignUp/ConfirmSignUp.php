<?php

declare(strict_types=1);

namespace App\Application\Users\ConfirmSignUp;

//use App\Domain\Model\Identity\User\User;
//use App\Domain\Model\OutdatedTokenException;
//use App\Domain\Model\UnconfirmedUser\UnconfirmedUserNotFoundException;
//use App\Domain\Model\Identity\UnconfirmedUser\UnconfirmedUserRepository;
use App\Application\ApplicationService;

class ConfirmSignUp {

    public function execute(string $token) : ConfirmSignUpResponse {
//        $unconfirmedUser = $this->unconfirmedUsers->getByToken($token);
//
//        if(!$unconfirmedUser)
//            throw new UnconfirmedUserNotFoundException('Wrong token');
//        else if($unconfirmedUser->isConfirmed())
//            throw new OutdatedTokenException('Expired token');
//
//        $user = new User(
//            $unconfirmedUser->email(),
//            $unconfirmedUser->password(),
//            $unconfirmedUser->firstName(),
//            $unconfirmedUser->lastName(),
//            $unconfirmedUser->gender(),
//            $unconfirmedUser->birthday(),
//            $unconfirmedUser->roles()
//        );
//
//        $unconfirmedUser->confirmRegistration(true);
//        $this->users->add($user);
//        $this->users->flush(); // этот flush() действует на $user и $unconfirmedUser
        $user = null;
        return new ConfirmSignUpResponse($user);
    }
}
