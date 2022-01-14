<?php

namespace App\Middlewares;

use Slim\Handlers\ErrorHandler;
use App\Application\Exceptions\ForbiddenException;
use App\Permissions\Exceptions\NotAuthenticatedException;
use App\Domain\Model\Identity\User\UserNotFoundException;
use Slim\Exception\HttpNotFoundException;
use App\Domain\Model\BadCredentialsException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use App\Application\BadRequestException;
use App\Application\Exceptions\NotExistException;
use App\Application\ErrorCodes;
use App\Application\Exceptions\ConflictException;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\Exceptions\MalformedRequestException;
use App\Application\Exceptions\ValidationException;
use App\Application\DomainException;
use Slim\Exception\HttpMethodNotAllowedException;

class ErrorsHandler extends ErrorHandler {
    protected $contentType = 'application/json';
    private array $descriptions = [ // Мне кажется, что описания ошибок не нужны в коде, ведь они не испльзуются
        1 => 'It is impossible to offer friendship to himself',
        2 => 'User not found by id from request body or query string'
    ];
    
//    protected function logError(string $error): void
//    {
//        // Insert custom error logging function.
//    }
    
    public function __invoke(
        ServerRequestInterface $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        $headers = ['Content-Language' => 'en'];

        if ($logErrors) {
           // echo 'dasdas';exit();
            //$this->writeToErrorLog();
        }
        
        try {
            throw $exception;
        } catch (BadRequestException $ex) {
            $statusCode = 400;
        } catch (BadCredentialsException | NotAuthenticatedException $ex) {
            $statusCode = 401; // пользователь не вошел систему и ему запрещен доступ до чего-то. Мне кажется, что это НЕ уместно возвращать при входе в систему, потому что для входа в систему аутентификция не нужна. Это нужно только в случае если НЕ аутентифицированный пользователь запрашивает ресурс, который доступен только для аутентифицированных пользователей. Это уместно, если ресурс доступен только для аутентифицированных пользователей и клиент даже присылает токен в запросе, но он недействительный.
        } catch (ForbiddenException $ex) {
            $statusCode = 403;                                                    // пользователь вошел в систему, но ему запрещен доступ до чего-то
        } catch (HttpNotFoundException | NotExistException $ex) {
            $statusCode = 404;
        } catch (HttpMethodNotAllowedException $ex) {
            $statusCode = 405;
        } catch (ConflictException $ex) {
            $statusCode = 409;
        } catch (ValidationException | UnprocessableRequestException | DomainException $ex) {
            $statusCode = 422;
        } catch (\Assert\LazyAssertionException $ex) {
            $errors = [];
            /** @var \Assert\InvalidArgumentException $error */
            foreach($ex->getErrorExceptions() as $error) {
                $errors[] = ['field' => $error->getPropertyPath(), 'message' => $error->getMessage()];
            }
            $this->logError('validation error');
            $payload = ['errors' => $errors, 'errorCode' => $exception->getCode()];
            return new JsonResponse($payload, 422, $headers);
        // возможно стоит ловить Logic исключения отдельно
        } catch (\Throwable $ex) {
            $message = 'Message: ' . $ex->getMessage() . ', Line: ' . $ex->getLine() . ', File: ' . $ex->getFile() . ', Exception name: ' . get_class($ex);
            $this->logError($message);
            $payload = [
                'error' => $message
            ];
            return new JsonResponse($payload, 500, $headers);
        }        
        
        $payload = [
            'errorCode' => $exception->getCode(),
            'message' => $exception->getMessage()
        ];
        
        return new JsonResponse($payload, $statusCode, $headers);
    }
}