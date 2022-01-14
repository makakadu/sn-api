<?php
declare(strict_types=1);

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Domain\Model\AuthenticationService;
use Tuupola\Middleware\JwtAuthentication\RequestPathRule;
use App\Auth\JwtAuthHS256;

class JwtAuthMiddleware implements MiddlewareInterface {

    private array $ignore;
    private string $path;
    private JwtAuthHS256 $jwtAuthHS256;
    private AuthenticationService $authService;
    
    public function __construct(AuthenticationService $authService, array $options) {
        if(!isset($options['secret'])) {
            throw new \Exception('secret is required');
        }
        $lifetime = $options['lifetime'] ?? 10000;
        $this->jwtAuthHS256 = new JwtAuthHS256('localhost:1338', $options['secret'], $lifetime);
        $this->authService = $authService;
        $this->ignore = $options['ignore'] ?? [];
        $this->path = $options['path'] ?? '/';
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
//        $pathRule = new RequestPathRule(['path' => $this->path, 'ignore' => $this->ignore]);
//        if(!$authHeader && $pathRule($request)) {
//            throw new NotAuthenticatedException('Token is not provided');
//        }
        $authHeader = $request->getHeader('Authorization');

        if(isset($authHeader[0])) {
            $authHeaderValue = $authHeader[0];

            if(!$authHeaderValue) {
                $this->throwNotFound();
            }
            $authorization = explode(' ', $authHeaderValue);

            if(!isset($authorization[0]) || $authorization[0] !== 'bearer') {
                $this->throwNotFound();
            } 
            elseif(!isset($authorization[1])) {
                $this->throwNotFound();
            }
            $token = $authorization[1];

            try {
                $decodedToken = $this->jwtAuthHS256->decodeToken($token);
                $userId = $decodedToken['data']['userId'];
                $request = $request->withAttribute('token', ['data' => ['userId' => $userId]]);
            } catch (\Exception $ex) {
                throw new \App\Application\Exceptions\NotAuthenticatedException2(
                    ['message' => 'Authorization token error. ' . $ex->getMessage()]
                );
            }
        } else {
            $request = $request->withAttribute('token', ['data' => ['userId' => null]]);
        }

        return $handler->handle($request);
    }
    
    function throwNotFound() {
        throw new \App\Application\Exceptions\NotAuthenticatedException2(
            ['message' => 'Token not found']
        );
    }
}
