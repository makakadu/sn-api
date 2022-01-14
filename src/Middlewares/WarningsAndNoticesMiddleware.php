<?php
declare(strict_types=1);
namespace App\Middlewares;

use App\Factory\LoggerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final class WarningsAndNoticesMiddleware implements MiddlewareInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    //private bool $printError;

    /**
     * The constructor.
     *
     * @param LoggerFactory $loggerFactory The logger
     */
    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory
            ->addFileHandler('warnings_and_notices.log')
            ->createInstance('warnings_and_notices_handler_middleware');
        //$this->printError = $printError;
    }

    /**
     * Invoke middleware.
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @return ResponseInterface The response
     */
    public function process(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface
    {
        set_error_handler(
            function ($errno, $errstr, $errfile, $errline) {
                echo (22 & 0);exit();
                
                switch ($errno) {
                    case E_USER_ERROR:
                        $this->logger->error(
                            "Error number [$errno] $errstr on line $errline in file $errfile"
                        );
                        break;
                    case E_USER_WARNING:
                        $this->logger->warning(
                            "Error number [$errno] $errstr on line $errline in file $errfile"
                        );
                        break;
                    default:
                        $this->logger->notice(
                            "Error number [$errno] $errstr on line $errline in file $errfile"
                        );
                        break;
                }
                
                $errorMessage = "$errstr on line $errline in file $errfile";

//                if(!$this->showMessage) {
//                    $message = "Internal error";
//                }
                
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['error' => $errorMessage]);
                exit();
                //return true;
            },
            E_ALL
        );

        return $handler->handle($request);
    }
}
