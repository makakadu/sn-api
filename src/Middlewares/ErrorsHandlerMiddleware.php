<?php
declare(strict_types=1);
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
use Psr\Http\Message\ResponseFactoryInterface;
use App\Application\Exceptions\ApplicationException;
use App\Factory\LoggerFactory;
use Psr\Log\LoggerInterface;

class ErrorsHandlerMiddleware implements MiddlewareInterface {
    
    private ResponseFactoryInterface $responseFactory;
    private LoggerInterface $logger;
    private bool $isProduction;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        LoggerFactory $loggerFactory,
        bool $isProduction
    ) {
        $this->responseFactory = $responseFactory;
        $this->isProduction = $isProduction;
        $this->logger = $loggerFactory
            ->addFileHandler('errors.log')
            ->createInstance('errors_handler_middleware');
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        //print_r($request);exit();
        try {
            return $handler->handle($request);
        } 
        catch(\App\Domain\Model\DomainExceptionAlt $exception) {
            $response = $this->responseFactory->createResponse()->withStatus(422);
            $response->getBody()->write(\json_encode(
                $exception->payload()
            ));
            return $response->withHeader('Content-Type', 'application/json');
        }
        catch(\App\Application\Exceptions\NotAuthenticatedException2 $exception) {
            $response = $this->responseFactory->createResponse()->withStatus(401);
            $response->getBody()->write(\json_encode(
                $exception->payload()
            ));
            return $response->withHeader('Content-Type', 'application/json');
        }
        catch(\App\Application\Exceptions\ConflictException2 $exception) {
            $response = $this->responseFactory->createResponse()->withStatus(409);
            $response->getBody()->write(\json_encode(
                $exception->payload()
            ));
            return $response->withHeader('Content-Type', 'application/json');
        }
        catch (ApplicationException $exception) {
            $statusCode = $exception->statusCode();

            $response = $this->responseFactory->createResponse()->withStatus($statusCode);
            $errorMessage = sprintf('%s', $exception->getMessage());

            $this->logger->error($errorMessage);

            $response->getBody()->write(\json_encode([
                'code' => $exception->getCode(),
                'errors' => $this->isProduction
                    ? $errorMessage
                    : '(application exception)'.$errorMessage.' (line)'.$exception->getLine().' (file)'.$exception->getFile()
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        catch (HttpMethodNotAllowedException $ex) {
            
            $response = $this->responseFactory->createResponse()->withStatus(405);
            $response->getBody()->write(\json_encode([
                'errors' => [$ex->getMessage()]
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        catch (HttpNotFoundException $ex) {

            $response = $this->responseFactory->createResponse()->withStatus(404);
            $response->getBody()->write(\json_encode([
                'code' => 1234,
                'errors' => [$ex->getMessage()]
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        catch (\Throwable $error) {
            $response = $this->responseFactory->createResponse()->withStatus(500);
            $response->getBody()->write(\json_encode([
                'code' => 500,
                'errors' => $this->isProduction
                    ? ['Internal server error']
                    : [$error->getMessage() . ' on file '. $error->getFile() . ' on line ' . $error->getLine()]
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        
        $headers = [];
        try {
            return $handler->handle($request);
        } catch (BadRequestException $ex) {
            $statusCode = 400;
        } catch (BadCredentialsException | NotAuthenticatedException $ex) {
            $statusCode = 401;
        } catch (ForbiddenException $ex) {
            $statusCode = 403;
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
        } catch (\Throwable $ex) {
            $message = 'Message: ' . $ex->getMessage() . ', Line: ' . $ex->getLine() . ', File: ' . $ex->getFile() . ', Exception name: ' . get_class($ex);
            $this->logError($message);
            $payload = [
                'error' => $message
            ];
            return new JsonResponse($payload, 500, $headers);
        }        
        
        $payload = [
            'errorCode' => $ex->getCode(),
            'message' => $ex->getMessage()
        ];
        
        return new JsonResponse($payload, $statusCode, $headers);
    }
}