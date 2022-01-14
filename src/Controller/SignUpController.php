<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Users\SignUp\SignUpRequest;
use App\Application\ConfirmSignUp\ConfirmSignUp;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\TransactionalApplicationService;

class SignUpController extends AbstractController {
    /**
     * @Inject
     * @var \App\Application\TransactionalSession
     */
    private $transactionalSession;
    
    /**
     * @Inject
     * @var \App\Application\Users\SignUp\SignUp
     */
    private $signUp;

    function getSignUpFormNew(Request $request, Response $response) : Response {
        $responseBody = $this->templateEngine->render('sign_up.twig', array());
        $response->getBody()->write($responseBody);
        $this->emitter->emit($response);
        exit();
    }

    function getSignUpForm() {
        if($this->sessionService->currentUserRoles()[0] != 'guest') {
            $currentUserId = $this->sessionService->currentUserId();
            header("Location: /users/{$currentUserId}");
        }
        $this->emitHTML(200, [], [], 'sign_up.twig');
    }

    function signUp(Request $request, Response $response) : Response {        
        $requestBody = (array)$request->getParsedBody() ?? [];

        $this->failIfRequiredParamWasNotPassed(
            $requestBody, ['email', 'password', 'repeatedPassword', 'firstname', 'lastname', 'username', 'sex', 'birthday', 'language']
        );

        $requestDTO = new SignUpRequest(
            $request->getAttribute('token')['data']['userId'],
            $requestBody['email'],
            $requestBody['password'],
            $requestBody['repeatedPassword'],
            $requestBody['firstname'],
            $requestBody['lastname'],
            $requestBody['username'],
            $requestBody['sex'],
            $requestBody['birthday'],
            $requestBody['language']
        );

        $useCase = new TransactionalApplicationService(
            $this->signUp, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO, 201);
    }

    function confirmSignup(string $token) {
        $useCase = new ConfirmSignUp($this->unconfirmedUserRepository, $this->users);

        try{
            $responseDTO = $useCase->execute(new ConfirmSignUpRequest($token));
            $responseData = (array)$responseDTO;
            $statusCode = 201;
        } catch (UnconfirmedUserNotFoundException | OutdatedTokenException $ex) {
            $responseData['errors'][] = $ex->getMessage();
            $statusCode = 400;
        } catch (NotUniqueValueException $ex) { // Маловероятный случай, но возможный
            $responseData['errors'][] = $ex->getMessage();
            $statusCode = 409;
        }

        $headers = [];
        if($this->isClientAcceptJSON()) {
            if($responseData->id) {
                $headers['Location'] = "users/{$responseData->id}";
            }
            $this->emitJSON($statusCode, $headers, $responseData);
        } else {
            $this->emitHTML($statusCode, $headers, $responseData, 'finished_registration.twig');
        }
    }
}