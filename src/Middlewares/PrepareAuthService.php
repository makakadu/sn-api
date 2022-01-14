<?php
declare(strict_types=1);

namespace App\Middlewares;

use App\Domain\Model\AuthenticationService;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class PrepareAuthService implements MiddlewareInterface {
    private AuthenticationService $authService;

    public function __construct(AuthenticationService $authService) {
        $this->authService = $authService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
        //echo 'sdfsdff';exit();
        $id = $request->getAttribute('token')['data']->userId;
        $roles = $request->getAttribute('token')['data']->userRoles ?? ['guest'];
        $expiresIn = $request->getAttribute('token')['exp'] ?? null;
        $this->authService->setCurrentUserId($id);
        $this->authService->setCurrentUserRoles($roles);
        $this->authService->setExpiresIn($expiresIn);
        
        return $handler->handle($request);
    }
}
