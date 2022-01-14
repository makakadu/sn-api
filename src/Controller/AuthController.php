<?php
declare(strict_types=1);
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Users\Auth\GetMe\GetMeRequest;
use App\Application\TransactionalApplicationService;

class AuthController extends AbstractController {
    /**
     * @Inject
     * @var \App\Application\TransactionalSession
     */
    private $transactionalSession;
    
//    /**
//     * @Inject
//     * @var \App\Application\FacebookAuth\FacebookAuth
//     */
//    private $fbAuth;
    /**
     * @Inject
     * @var \App\Application\Users\SignIn\SignIn
     */
    private $signIn;
    /**
     * @Inject
     * @var \App\Application\Users\Auth\GetMe\GetMe
     */
    private $getMe; 
    
    private $fakeId = "01fkeg2e04mp80vj7z62dff436";

    function signIn(Request $request, Response $response) : Response {
        $requestBody = (array)$request->getParsedBody() ?? [];
        
        $requesterId = $request->getAttribute('token')['data']['userId'];
        $email = isset($requestBody['email']) ? $requestBody['email'] : null;
        $password = isset($requestBody['password']) ? $requestBody['password'] : null;
        $request = new \App\Application\Users\SignIn\SignInRequest($requesterId, $email, $password);
        return $this->prepareResponse(
            $response, $this->signIn->execute($request), 201
        );
    }

//    function facebookSignIn(Request $request, Response $response) : Response {
//        $code = $request->getQueryParams()['code'] ?? null;
//        if(!$code) return respondWithJSON($response, 'code not provided', 401);
//
//        $dto = $this->fbAuth->execute($code);
//        return $response->withHeader(
//            'Location', "http://localhost:3000/loginfb?jwt=" . $dto['jwt']
//        );
//    }

    function getMe(Request $request, Response $response) : Response {
        $requestDTO = new GetMeRequest(
            $request->getAttribute('token')['data']->userId
        );
        $useCase = new TransactionalApplicationService(
            $this->getMe, $this->transactionalSession
        );
        //header("HTTP/1.1 500 Internal Server Error");exit();
        $responseDTO = $useCase->execute($requestDTO);

        return $this->prepareResponse($response, $responseDTO);
    }
}
