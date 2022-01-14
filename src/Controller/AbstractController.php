<?php

namespace App\Controller;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use App\Application\Exceptions\MalformedRequestException;

abstract class AbstractController {
//    /**
//     * @Inject
//     * @var \App\Domain\Model\Identity\User\UserRepository
//     */
//    protected $users;
    
    protected function rejectNotJSONContentType(): void {

        if(isset($this->serverRequest->getHeader('Content-type')[0]) && !$this->serverRequest->getHeader('Content-type')[0] == "application/json; charset=utf-8") {
            $responseBody['error'] = 'Accepting only json';
            $this->emitJSON(415, [], $responseBody);
        }
    }

    protected function isClientAcceptJSON(): bool {
        $needle = "application/json; charset=utf-8";
        return in_array($needle, $this->serverRequest->getHeader('Accept'));
    }

    protected function prepareResponse(ResponseInterface $response, $payload, $statusCode = 200) : ResponseInterface {
        if($payload) {
            $response->getBody()->write(\json_encode($payload));
        }
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
    
    protected function respond(ResponseInterface $response, $payload, $headers, $statusCode = 200) : ResponseInterface {
        if($payload) {
            $response->getBody()->write(\json_encode($payload));
        }
        
        if($headers) {
            foreach ($headers as $header) {
                //print_r($header);exit();
                $response = $response->withHeader($header['name'], $header['value']);
            }
        }
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }

    protected function emitHTML(int $statusCode, array $headers, array $responseData, string $templateName) {
        //$headers['Content-type'] = 'application/html; charset=utf-8';
//        $responseData = $this->resolveUserOrGuest($responseData);
//        $responseBody = $this->templateEngine->render($templateName, $responseData);
//        $response = new Response($statusCode, $headers, $responseBody);
//        $this->emitter->emit($response);
    }

    protected function decodeJSONRequestBody($serverRequest) {
        return \json_decode($serverRequest->getBody(), true);
        //return \json_decode($this->serverRequest->getBody()->getContents(), true);
    }
    
    /**
     * @param array<mixed> $body
     * @param array<mixed> $requiredParams
     * @throws MalformedRequestException
     */
    protected function failIfRequiredParamWasNotPassed(array $body, array $requiredParams) {
        $notPassed = [];
        foreach($requiredParams as $param) {
            if(!array_key_exists($param, $body)) {
                $notPassed[] = $param;
            }
        }
        
        if(count($notPassed)) {
            $message = count($notPassed) === 1
                ? "Parameter '".$notPassed[0]."' is required and should be in request body"
                : "Parameters: '".\implode(', ', $notPassed)."' are required and should be in request body" ;
            throw new MalformedRequestException($message);
        }
    }
}
