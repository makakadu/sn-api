<?php
declare(strict_types=1);

namespace App\Middlewares;

use App\Domain\Model\AuthenticationService;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Domain\Model\Users\User\UserRepository;

final class PrepareUser implements MiddlewareInterface {
    private UserRepository $users;

    public function __construct(UserRepository $users) {
        $this->users = $users;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
        
        return $handler->handle($request);
    }
}
