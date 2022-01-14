<?php
declare(strict_types=1);
namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EtagMiddleware implements MiddlewareInterface {

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        //print_r($request->getHeaders()['content-type']);exit();
//        if($request->getHeaders()['content-type'][0] !== 'application/json') {
//            echo 'hgfhghfh';exit();
//        }
        
        $response = $handler->handle($request);
        
        if($request->getMethod() === 'GET') {
            $eTagHeader = $request->getHeader('if-none-match');
            $clientEtag = isset($eTagHeader[0]) ? $eTagHeader[0] : null;

            $response->getBody()->rewind();
            $serverEtag = md5(serialize($response->getBody()->getContents()));
            $code = $clientEtag === $serverEtag ? 304 : 200;

            // Add ETag header if not already added
            if (!$response->hasHeader('Etag')) {
                $response = $response->withHeader('ETag', $serverEtag);
                $response = $response->withStatus($code);
            }
        }
        return $response;
    }

}
