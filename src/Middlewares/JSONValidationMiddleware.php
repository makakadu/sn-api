<?php

declare(strict_types=1);

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Application\Exceptions\MalformedRequestException;

class JSONValidationMiddleware implements MiddlewareInterface {

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
//        if($request->getMethod() === 'POST' || $request->getMethod() === 'PATCH' || $request->getMethod() === 'PUT') {
//            if($request->getParsedBody() === null) { 
//                throw new MalformedRequestException('Incorrect json syntax');
//            }
//        }
        return $handler->handle($request);
    }

}
