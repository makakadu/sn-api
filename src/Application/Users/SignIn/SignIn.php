<?php
declare(strict_types=1);
namespace App\Application\Users\SignIn;

use App\Auth\JwtAuthHS256;
use App\Domain\Model\Users\User\UserRepository;
//use App\Infrastructure\Authentication\AuthService;
use App\Application\Exceptions\ForbiddenException;
use App\Application\Exceptions\ValidationException;

class SignIn implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    //private AuthService $authService;
    private JwtAuthHS256 $jwtAuthHS256;
    
    public function __construct(JwtAuthHS256 $jwtAuthHS256, UserRepository $users) {
        $this->jwtAuthHS256 = $jwtAuthHS256;
        $this->users = $users;
        //$this->authService = $authService;
    }

    function execute(\App\Application\BaseRequest $request): SignInResponse  {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        if($requester) {
            throw new \App\Application\Exceptions\ForbiddenException(123, 'No rights');
        }
        
        $email = $request->email;
        $password = $request->password;

        if(!$email || !$password) {
            throw new ValidationException(13, 'Email and password are required');
        }
        
        $user = $this->users->getByEmail($email);
        
        if(!$user || !password_verify($password, $user->password()->value())) {
            throw new ForbiddenException(13, 'Incorrect email or password');
        }
        
        return new SignInResponse(
            "Successful login",
            $this->jwtAuthHS256->createJwt((string)$user->id(), ['user']),
            'Bearer',
            $this->jwtAuthHS256->getLifetime()
        );
    }
//
//    function validate(SignInRequest $signInData) {
//    }

}
