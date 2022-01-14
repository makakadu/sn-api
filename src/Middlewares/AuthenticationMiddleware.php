<?php
declare(strict_types=1);

namespace App\Middlewares;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Domain\Model\AuthenticationService;
use Tuupola\Middleware\JwtAuthentication\RequestPathRule;
use App\Application\Exceptions\NotAuthenticatedException;

final class AuthenticationMiddleware implements MiddlewareInterface {
    private array $ignore;
    private AuthenticationService $authService;

    public function __construct(AuthenticationService $authService, array $ignore) {
        $this->authService = $authService;
        $this->ignore = $ignore;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
        //echo 'sdfsdff';exit();
        $pathRule = new RequestPathRule([
            'path' => '/',
            'ignore' => $this->ignore
        ]);
        $token = $request->getAttribute('token');
        if(!$token  && $pathRule($request)) {
            throw new NotAuthenticatedException('Token is not provided');
        } else if($token) {
            $id = $request->getAttribute('token')['data']->userId;
            $roles = $request->getAttribute('token')['data']->userRoles;
            $expiresIn = $request->getAttribute('token')['exp'];
        } else {
            $id = null;
            $roles = ['guest'];
            $expiresIn = null;
        }
        
//        if(!$id && $pathRule($request)) { //RequestPathRule - Rule to decide by request path whether the request should be authenticated or not
//            throw new NotAuthenticatedException('Token is not provided');
//        }
        

        $this->authService->setCurrentUserId($id);
        $this->authService->setCurrentUserRoles($roles);
        $this->authService->setExpiresIn($expiresIn);
        
        return $handler->handle($request);
    }
}
