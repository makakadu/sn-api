<?php
declare(strict_types=1);
namespace App\Middlewares;

use App\Factory\LoggerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class ServerErrorsHandlerMiddleware implements MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;
    private LoggerInterface $logger;
    private bool $isProduction;

    public function __construct(ResponseFactoryInterface $responseFactory, LoggerFactory $loggerFactory, bool $isProduction)
    {
        $this->logger = $loggerFactory
            ->addFileHandler('server_errors.log')
            ->createInstance('server_errors_handler_middleware');
        $this->isProduction = $isProduction;
        $this->responseFactory = $responseFactory;
    }

    public function process(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface
    {
        $shutdown = function () {
            $error = error_get_last();
            //print_r($error);exit();
            
            if($error && $error['type'] === E_WARNING) {
                ob_end_clean();
                http_response_code(500);
                header('Content-Type: application/json');
                if($this->isProduction) {
                    echo json_encode(['Internal server error']);
                } else {
                    echo json_encode(['error' => $error['message'] . ', type ' . $error['type']. ' on file ' . $error['file'] . ' on line ' . $error['line']]);
                }
                exit();
            }
            
            if($error) {
                
                $error = error_get_last();
                $errorType = $error['type'];
                $errorMessage = $error['message'];
                $errorLine = $error['line'];
                $errorFile = $error['file'];
                
                $message = "(other) $errorMessage on line $errorLine in file $errorFile. (error type: $errorType)";
                $this->logger->error($message);
                //echo $errorType . '__' . $errorMessage;exit();
                if($this->isProduction) {
                    $message = "Internal server error";
                }
                
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['error' => $message]);
                exit();
            }
        };
        register_shutdown_function($shutdown);
        
        set_error_handler(
            function ($errno, $errstr, $errfile, $errline) {
                $errorMessage = "(error handler) $errstr on line $errline in file $errfile. (error type: $errno)";

                if($this->isProduction) { $errorMessage = "Internal server error"; }
                throw new \Exception($errorMessage);

            }, E_ALL
        );

        return $handler->handle($request);
    }
}
