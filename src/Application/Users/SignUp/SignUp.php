<?php
declare(strict_types=1);
namespace App\Application\Users\SignUp;

use Assert\Assert;
use App\Permissions\Permissions;
use App\Domain\Model\Users\Password;
use App\Domain\Model\Users\User\UserRepository;
use App\Application\ApplicationService;
use App\Domain\Model\Users\User\User;

class SignUp implements ApplicationService {
    use \App\Application\AppServiceTrait;
            
    public function __construct(UserRepository $users) {
        $this->users = $users;
    }
    
    public function execute(\App\Application\BaseRequest $request): \App\Application\BaseResponse {

        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        if($requester) {
            throw new \App\Application\Exceptions\ForbiddenException(123, 'No rights');
        }
        //print_r($this->authService->getCurrentUserRoles());exit();
//        $this->failIfNotAccess(Permissions::SIGN_UP);
//        $this->validate($signUpData);
//        $this->failIfEmailRegistered($signUpData['email']);

        $byEmail = $this->users->getByEmail($request->email);
        if($byEmail) {
            throw new \App\Application\Exceptions\ConflictException2(
                [
                    'code' => \App\Application\Errors::EMAIL_TAKEN,
                    'message' => 'Email taken'
                ]
            );
        }
        
        $byUsername = $this->users->getByUsername($request->username);

        if($byUsername) {
            throw new \App\Application\Exceptions\ConflictException2(
                [
                    'code' => \App\Application\Errors::USERNAME_TAKEN,
                    'message' => 'Username taken'
                ]
            );
        }
        
        if($request->password !== $request->repeatedPassword) {
            throw new \App\Application\Exceptions\ValidationException('Different passwords');
        }
        
        $hashedPassword = new Password($request->password);
        $user = new User(
            $request->email,
            $hashedPassword,
            $request->firstName,
            $request->lastName,
            new \App\Domain\Model\Users\User\Username($request->username),
            $request->sex,
            $request->language,
            $request->birthday,
            []
        );
        
        $this->users->add($user);
        try {
            $this->users->flush();
        } catch (\App\Application\Exceptions\UniqueConstraintViolationException $ex) {
            throw new \App\Application\Exceptions\ConflictException2(
                [
                    'code' => \App\Application\Errors::EMAIL_OR_USERNAME_TAKEN,
                    'message' => 'Email or username is taken'
                ]
            );
        }
        
        return new SignUpResponse('OK');
    }

//    function execute(array $signUpData): SignUpResponse {
//        //print_r($this->authService->getCurrentUserRoles());exit();
//        $this->failIfNotAccess(Permissions::SIGN_UP);
//        $this->validate($signUpData);
//        $this->failIfEmailRegistered($signUpData['email']);
//        
//        $user = new User(
//            $signUpData['email'], new Password($signUpData['password']),
//            $signUpData['firstname'], $signUpData['lastname'],
//            $signUpData['gender'], $signUpData['birthday'], ['user']
//        );
//        
////        $unconfirmedUser = new UnconfirmedUser(
////            $signUpData['email'], new HashedPassword($signUpData['password']),
////            $signUpData['firstname'], $signUpData['lastname'],
////            $signUpData['gender'], $signUpData['birthday'], ['user']
////        );
//
//        $this->users->add($unconfirmedUser);
//        $this->users->flush();
//        return new SignUpResponse(
//            $unconfirmedUser,
//            'Registration will end when user confirm his/her email'
//        );
//    }

    private function validate(array $signUpData) {
        //$birthday = $data['birthday']['month'] . $data['birthday']['day'] . $data['birthday']['year'];
        Assert::lazy()
            ->that($signUpData['email'], 'email')->string()->email()
            ->that($signUpData['firstname'], 'firstName')->string()->minLength(2)->maxLength(30)
            //->that('f', 'firstName')->string()->minLength(2)->maxLength(30)
            ->that($signUpData['lastname'], 'lastname')->string()->minLength(2)->maxLength(30)
            ->that($signUpData['password'], 'password')->string()->minLength(12)->maxLength(30)
            ->that($signUpData['gender'], 'gender')->string()->regex('/^(male|female)$/')
            ->verifyNow();
    }

}
